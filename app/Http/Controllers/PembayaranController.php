<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'ortu') {
            abort(403, 'Akses ditolak');
        }

        $pembayaran = Pembayaran::with(['siswa', 'jenisPembayaran'])
            ->orderByDesc('tanggal_bayar')
            ->get();

        return view('pembayaran.index', compact('pembayaran'));
    }

    public function verifikasi(Request $request)
    {
        $this->authorizeAdmin();

        $query = Pembayaran::with(['siswa', 'jenisPembayaran', 'tagihan'])
            ->where('status', 'pending')
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id');

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('siswa', function ($s) use ($keyword) {
                    $s->where('nama_siswa', 'like', '%' . $keyword . '%')
                        ->orWhere('nis', 'like', '%' . $keyword . '%');
                })->orWhereHas('jenisPembayaran', function ($j) use ($keyword) {
                    $j->where('nama_pembayaran', 'like', '%' . $keyword . '%');
                });
            });
        }

        $pending = $query->paginate(10)->withQueryString();

        return view('pembayaran.verifikasi', compact('pending'));
    }

    public function approve($id)
    {
        $this->authorizeAdmin();

        DB::beginTransaction();

        try {
            $pembayaran = Pembayaran::with('tagihan')->lockForUpdate()->findOrFail($id);

            if ($pembayaran->status !== 'pending') {
                return back()->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
            }

            $tagihan = Tagihan::lockForUpdate()->findOrFail($pembayaran->tagihan_id);

            if ((int) $tagihan->sisa_tagihan <= 0 || $tagihan->status === 'lunas') {
                return back()->with('error', 'Tagihan sudah lunas, tidak bisa approve pembayaran ini.');
            }

            if ((int) $pembayaran->nominal_bayar > (int) $tagihan->sisa_tagihan) {
                return back()->with('error', 'Nominal pending melebihi sisa tagihan saat ini. Silakan reject pembayaran ini.');
            }

            $tagihan->sisa_tagihan = (int) $tagihan->sisa_tagihan - (int) $pembayaran->nominal_bayar;
            $tagihan->status = ((int) $tagihan->sisa_tagihan === 0) ? 'lunas' : 'cicil';
            $tagihan->updated_user = auth()->user()->name ?? 'admin';
            $tagihan->save();

            $pembayaran->status = ((int) $tagihan->sisa_tagihan === 0) ? 'lunas' : 'cicil';
            $pembayaran->updated_user = auth()->user()->name ?? 'admin';
            $pembayaran->save();

            DB::commit();
            return back()->with('success', 'Pembayaran pending berhasil di-approve.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve pembayaran.');
        }
    }

    public function reject(Request $request, $id)
    {
        $this->authorizeAdmin();

        $request->validate([
            'alasan_reject' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($id);

            if ($pembayaran->status !== 'pending') {
                return back()->with('error', 'Pembayaran ini sudah diproses sebelumnya.');
            }

            $keteranganReject = trim((string) $request->alasan_reject);
            if ($keteranganReject !== '') {
                $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . 'Ditolak: ' . $keteranganReject);
            }

            $pembayaran->status = 'ditolak';
            $pembayaran->updated_user = auth()->user()->name ?? 'admin';
            $pembayaran->save();

            DB::commit();
            return back()->with('success', 'Pembayaran pending berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak pembayaran.');
        }
    }

    public function kwitansi($id)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Akses ditolak');
        }

        $pembayaran = Pembayaran::with(['siswa', 'jenisPembayaran', 'tagihan'])->findOrFail($id);

        if ($pembayaran->status !== 'lunas') {
            return back()->with('error', 'Kwitansi hanya tersedia untuk pembayaran yang sudah lunas.');
        }

        if ($user->role === 'ortu' && (int) $user->siswa_id !== (int) $pembayaran->siswa_id) {
            abort(403, 'Akses ditolak');
        }

        $pdf = Pdf::loadView('pembayaran.kwitansi_pdf', [
            'pembayaran' => $pembayaran,
            'petugas' => $pembayaran->updated_user ?: ($pembayaran->created_user ?: 'Admin'),
        ])->setPaper('a5', 'portrait');

        $filename = 'kwitansi-' . str_pad((string) $pembayaran->id, 5, '0', STR_PAD_LEFT) . '.pdf';
        $pdfBase64 = base64_encode($pdf->output());

        return view('pembayaran.kwitansi_preview', [
            'pdfBase64' => $pdfBase64,
            'filename' => $filename,
        ]);
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }
    }
}
