<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuickPayController extends Controller
{
    /**
     * Tampilkan halaman Quick Pay.
     * - Admin: bisa pilih semua siswa
     * - Ortu: otomatis ke siswa yang terhubung
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;

        // Ortu: langsung ke siswa nya
        if ($role === 'ortu') {
            if (!$user->siswa_id) {
                return redirect()->route('dashboard')
                    ->with('error', 'Akun ortu belum terhubung ke data siswa.');
            }

            $siswa = Siswa::with('kelas')->findOrFail($user->siswa_id);
            $siswaTerpilih = $siswa;
            $siswaList     = collect([$siswa]);
        } else {
            // Admin: bisa pilih siswa
            $siswaList     = Siswa::with('kelas')->orderBy('nama_siswa')->get();
            $siswaId       = $request->get('siswa_id');
            $siswaTerpilih = $siswaId ? Siswa::with('kelas')->find($siswaId) : null;
        }

        // Ambil tagihan belum lunas dari siswa terpilih
        $tagihanList = [];
        if ($siswaTerpilih) {
            $tagihanList = Tagihan::with('jenisPembayaran')
                ->where('siswa_id', $siswaTerpilih->id)
                ->where('status', '!=', 'lunas')
                ->orderBy('periode_tahun')
                ->orderBy('periode_bulan')
                ->get();
        }

        return view('quick-pay.index', compact('siswaList', 'siswaTerpilih', 'tagihanList', 'role'));
    }

    /**
     * Proses bayar tagihan dari Quick Pay (sama seperti TagihanController@bayar)
     * Mendukung upload struk.
     */
    public function bayar(Request $request, $id)
    {
        $user = Auth::user();
        $role = $user->role;

        $request->validate([
            'nominal_bayar' => 'required|numeric|min:1',
            'metode_bayar'  => 'required|in:cash,transfer',
            'keterangan'    => 'nullable|string',
            'upload_foto'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $tagihan = Tagihan::findOrFail($id);

        // Ortu hanya bisa bayar tagihan milik siswa nya
        if ($role === 'ortu' && (int) $tagihan->siswa_id !== (int) $user->siswa_id) {
            abort(403, 'Akses ditolak');
        }

        if ((int) $request->nominal_bayar > (int) $tagihan->sisa_tagihan) {
            return back()->with('error', 'Nominal melebihi sisa tagihan!');
        }

        DB::beginTransaction();
        try {
            // Upload struk
            $pathFoto = null;
            if ($request->hasFile('upload_foto')) {
                $folder = public_path('uploads/pembayaran');
                if (!is_dir($folder)) mkdir($folder, 0755, true);
                $file = $request->file('upload_foto');
                $filename = 'bukti_' . now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($folder, $filename);
                $pathFoto = 'uploads/pembayaran/' . $filename;
            }

            $sisaSetelahBayar = (int) $tagihan->sisa_tagihan - (int) $request->nominal_bayar;
            $statusBaru       = ($sisaSetelahBayar === 0) ? 'lunas' : 'cicil';

            // Ortu: status pending, butuh verifikasi admin
            if ($role === 'ortu') {
                $statusBaru = 'pending';
                $sisaSetelahBayar = (int) $tagihan->sisa_tagihan; // sisa belum berubah sampai diverifikasi
            }

            $pembayaranBaru = Pembayaran::create([
                'tagihan_id'          => $tagihan->id,
                'siswa_id'            => $tagihan->siswa_id,
                'jenis_pembayaran_id' => $tagihan->jenis_pembayaran_id,
                'tanggal_bayar'       => now(),
                'nominal_bayar'       => $request->nominal_bayar,
                'metode_bayar'        => $request->metode_bayar,
                'status'              => $statusBaru,
                'keterangan'          => $request->keterangan,
                'upload_foto'         => $pathFoto,
                'created_user'        => $user->name ?? 'user',
            ]);

            // Update tagihan hanya jika bukan pending
            if ($role !== 'ortu') {
                $tagihan->sisa_tagihan = $sisaSetelahBayar;
                $tagihan->status       = $statusBaru;
                $tagihan->save();
            }

            DB::commit();

            $pesan = $role === 'ortu'
                ? 'Pembayaran berhasil diajukan dan menunggu verifikasi admin.'
                : 'Pembayaran berhasil!';

            return redirect()->back()
                ->with('success', $pesan)
                ->with('pembayaran_baru_id', $statusBaru !== 'pending' ? $pembayaranBaru->id : null)
                ->with('pembayaran_baru_status', $statusBaru);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pembayaran gagal! ' . $e->getMessage());
        }
    }
}
