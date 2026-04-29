<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->role === 'ortu') {
            abort(403, 'Akses ditolak');
        }

        $filters = $this->extractFilters($request);
        $perPage = $request->get('per_page', '10');
        $allowedPerPage = ['10', '20', '30', 'all'];
        if (!in_array((string) $perPage, $allowedPerPage, true)) {
            $perPage = '10';
        }

        $queryPembayaran = Pembayaran::with(['siswa', 'jenisPembayaran'])
            ->filter($filters)
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id');

        if ($perPage === 'all') {
            $totalData = (clone $queryPembayaran)->count();
            $pembayaran = $queryPembayaran->paginate($totalData > 0 ? $totalData : 1)->withQueryString();
        } else {
            $pembayaran = $queryPembayaran->paginate((int) $perPage)->withQueryString();
        }

        $filterSiswa = Siswa::with('kelas')
            ->orderBy('nama_siswa')
            ->get(['id', 'nama_siswa', 'nis', 'kelas_id']);

        $filterKelas = Kelas::orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        return view('pembayaran.index', compact('pembayaran', 'filterSiswa', 'filterKelas', 'filters', 'perPage'));
    }

    public function export(Request $request)
    {
        if (auth()->check() && auth()->user()->role === 'ortu') {
            abort(403, 'Akses ditolak');
        }

        $filters = $this->extractFilters($request);

        $pembayaran = Pembayaran::with(['siswa.kelas', 'jenisPembayaran'])
            ->filter($filters)
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $filename = 'data-pembayaran-' . now()->format('Ymd_His') . '.xls';
        $html = view('exports.pembayaran_index', [
            'pembayaran' => $pembayaran,
            'filters' => $filters,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function verifikasi(Request $request)
    {
        $this->authorizeAdmin();
        $perPage = $request->get('per_page', '10');
        $allowedPerPage = ['10', '20', '30', 'all'];
        if (!in_array((string) $perPage, $allowedPerPage, true)) {
            $perPage = '10';
        }

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

        if ($perPage === 'all') {
            $totalData = (clone $query)->count();
            $pending = $query->paginate($totalData > 0 ? $totalData : 1)->withQueryString();
        } else {
            $pending = $query->paginate((int) $perPage)->withQueryString();
        }

        return view('pembayaran.verifikasi', compact('pending', 'perPage'));
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
            $catatanApprove = 'Disetujui admin pada ' . now()->format('d-m-Y H:i');
            $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . $catatanApprove);
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
            } else {
                $pembayaran->keterangan = trim(($pembayaran->keterangan ? $pembayaran->keterangan . ' | ' : '') . 'Ditolak admin tanpa keterangan');
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

        if (!in_array($pembayaran->status, ['lunas', 'cicil'], true)) {
            return back()->with('error', 'Kwitansi hanya tersedia untuk pembayaran yang sudah di-approve.');
        }

        if ($user->role === 'ortu' && (int) $user->siswa_id !== (int) $pembayaran->siswa_id) {
            abort(403, 'Akses ditolak');
        }

        $riwayatCicilanSebelumnya = Pembayaran::with('tagihan')
            ->where('tagihan_id', $pembayaran->tagihan_id)
            ->whereIn('status', ['lunas', 'cicil'])
            ->where(function ($q) use ($pembayaran) {
                $q->whereDate('tanggal_bayar', '<', $pembayaran->tanggal_bayar)
                    ->orWhere(function ($q2) use ($pembayaran) {
                        $q2->whereDate('tanggal_bayar', $pembayaran->tanggal_bayar)
                            ->where('id', '<', $pembayaran->id);
                    });
            })
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $maksCicilanDitampilkan = 4;
        $riwayatCicilanDitampilkan = $riwayatCicilanSebelumnya
            ->take($maksCicilanDitampilkan)
            ->sortBy([
                ['tanggal_bayar', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
        $jumlahCicilanDisembunyikan = max(0, $riwayatCicilanSebelumnya->count() - $maksCicilanDitampilkan);

        $totalCicilanSebelumnya = (int) $riwayatCicilanSebelumnya->sum('nominal_bayar');
        $totalTagihanAwal = (int) optional($pembayaran->tagihan)->nominal_tagihan;
        $sisaSetelahPembayaranIni = (int) optional($pembayaran->tagihan)->sisa_tagihan;

        $pdf = Pdf::loadView('pembayaran.kwitansi_pdf', [
            'pembayaran' => $pembayaran,
            'petugas' => $pembayaran->updated_user ?: ($pembayaran->created_user ?: 'Admin'),
            'totalTagihanAwal' => $totalTagihanAwal,
            'riwayatCicilanSebelumnya' => $riwayatCicilanDitampilkan,
            'jumlahCicilanDisembunyikan' => $jumlahCicilanDisembunyikan,
            'totalCicilanSebelumnya' => $totalCicilanSebelumnya,
            'sisaSetelahPembayaranIni' => $sisaSetelahPembayaranIni,
        ])->setPaper('a5', 'portrait');

        $filename = 'kwitansi-' . str_pad((string) $pembayaran->id, 5, '0', STR_PAD_LEFT) . '.pdf';
        $pdfBase64 = base64_encode($pdf->output());

        return view('pembayaran.kwitansi_preview', [
            'pdfBase64' => $pdfBase64,
            'filename' => $filename,
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

        DB::beginTransaction();

        try {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($id);
            $tagihan = null;

            if ($pembayaran->tagihan_id) {
                $tagihan = Tagihan::lockForUpdate()->find($pembayaran->tagihan_id);
            }

            $uploadPath = $pembayaran->upload_foto;
            $statusPembayaranDihapus = (string) $pembayaran->status;
            $tagihanId = $pembayaran->tagihan_id;

            $pembayaran->delete();

            if ($tagihan && in_array($statusPembayaranDihapus, ['lunas', 'cicil'], true)) {
                $totalDibayarValid = (int) Pembayaran::where('tagihan_id', $tagihanId)
                    ->whereIn('status', ['lunas', 'cicil'])
                    ->sum('nominal_bayar');

                $sisaTagihan = (int) $tagihan->nominal_tagihan - $totalDibayarValid;
                if ($sisaTagihan < 0) {
                    $sisaTagihan = 0;
                }

                if ($sisaTagihan === (int) $tagihan->nominal_tagihan) {
                    $statusTagihan = 'belum_bayar';
                } elseif ($sisaTagihan === 0) {
                    $statusTagihan = 'lunas';
                } else {
                    $statusTagihan = 'cicil';
                }

                $tagihan->sisa_tagihan = $sisaTagihan;
                $tagihan->status = $statusTagihan;
                $tagihan->updated_user = auth()->user()->name ?? 'admin';
                $tagihan->save();
            }

            DB::commit();

            if (!empty($uploadPath)) {
                $absoluteUploadPath = public_path($uploadPath);
                if (File::exists($absoluteUploadPath)) {
                    File::delete($absoluteUploadPath);
                }
            }

            return back()->with('success', 'Data pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembayaran.');
        }
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }
    }

    private function extractFilters(Request $request): array
    {
        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSelesai = $request->get('tanggal_selesai');

        if ($tanggalMulai && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggalMulai)) {
            $tanggalMulai = null;
        }
        if ($tanggalSelesai && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggalSelesai)) {
            $tanggalSelesai = null;
        }

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        return [
            'siswa_id' => $request->get('siswa_id'),
            'kelas_id' => $request->get('kelas_id'),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status' => $request->get('status'),
            'metode_bayar' => $request->get('metode_bayar'),
        ];
    }
}
