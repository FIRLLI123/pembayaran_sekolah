<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\PembayaranResource;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Services\PembayaranService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    private $pembayaranService;

    public function __construct(PembayaranService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);

        $pembayaran = Pembayaran::with(['siswa.kelas', 'jenisPembayaran', 'tagihan'])
            ->filter($filters)
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data pembayaran berhasil diambil.',
            'data' => PembayaranResource::collection($pembayaran->items()),
            'meta' => [
                'current_page' => $pembayaran->currentPage(),
                'last_page' => $pembayaran->lastPage(),
                'per_page' => $pembayaran->perPage(),
                'total' => $pembayaran->total(),
            ],
        ]);
    }

    public function bayar(Request $request, Tagihan $tagihan): JsonResponse
    {
        $validated = $request->validate([
            'nominal_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:cash,transfer',
            'keterangan' => 'nullable|string',
            'upload_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('upload_foto')) {
            $validated['upload_foto'] = $request->file('upload_foto');
        }

        try {
            $pembayaran = $this->pembayaranService->bayarTagihan(
                $tagihan,
                $validated,
                auth()->user()->name ?? 'admin'
            );

            return response()->json([
                'message' => 'Pembayaran berhasil diproses.',
                'data' => new PembayaranResource($pembayaran->load(['siswa.kelas', 'jenisPembayaran', 'tagihan'])),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function multiBayar(Request $request, Siswa $siswa): JsonResponse
    {
        $validated = $request->validate([
            'total_bayar' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:cash,transfer',
        ]);

        $result = $this->pembayaranService->multiBayar(
            $siswa,
            [
                'total_bayar' => (int) str_replace('.', '', $validated['total_bayar']),
                'metode_bayar' => $validated['metode_bayar'],
            ],
            auth()->user()->name ?? 'admin'
        );

        $paymentIds = collect($result['payments'])->pluck('id')->all();
        $payments = Pembayaran::with(['siswa.kelas', 'jenisPembayaran', 'tagihan'])
            ->whereIn('id', $paymentIds)
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Multi pembayaran berhasil diproses.',
            'data' => [
                'payments' => PembayaranResource::collection($payments),
                'sisa_dana' => (int) $result['sisa_dana'],
            ],
        ], 201);
    }

    public function verifikasi(Request $request): JsonResponse
    {
        $query = Pembayaran::with(['siswa.kelas', 'jenisPembayaran', 'tagihan'])
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

        $pending = $query->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Daftar pembayaran pending berhasil diambil.',
            'data' => PembayaranResource::collection($pending->items()),
            'meta' => [
                'current_page' => $pending->currentPage(),
                'last_page' => $pending->lastPage(),
                'per_page' => $pending->perPage(),
                'total' => $pending->total(),
            ],
        ]);
    }

    public function approve(Pembayaran $pembayaran): JsonResponse
    {
        try {
            $approved = $this->pembayaranService->approve($pembayaran, auth()->user()->name ?? 'admin');

            return response()->json([
                'message' => 'Pembayaran pending berhasil di-approve.',
                'data' => new PembayaranResource($approved->load(['siswa.kelas', 'jenisPembayaran', 'tagihan'])),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        $validated = $request->validate([
            'alasan_reject' => 'nullable|string|max:255',
        ]);

        try {
            $rejected = $this->pembayaranService->reject(
                $pembayaran,
                isset($validated['alasan_reject']) ? $validated['alasan_reject'] : null,
                auth()->user()->name ?? 'admin'
            );

            return response()->json([
                'message' => 'Pembayaran pending berhasil ditolak.',
                'data' => new PembayaranResource($rejected->load(['siswa.kelas', 'jenisPembayaran', 'tagihan'])),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Pembayaran $pembayaran): JsonResponse
    {
        $this->pembayaranService->delete($pembayaran, auth()->user()->name ?? 'admin');

        return response()->json([
            'message' => 'Data pembayaran berhasil dihapus.',
        ]);
    }

    public function kwitansi(Pembayaran $pembayaran): JsonResponse
    {
        $pembayaran->load(['siswa', 'jenisPembayaran', 'tagihan']);

        if (!in_array($pembayaran->status, ['lunas', 'cicil'], true)) {
            return response()->json([
                'message' => 'Kwitansi hanya tersedia untuk pembayaran yang sudah di-approve.',
            ], 422);
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

        return response()->json([
            'message' => 'Kwitansi berhasil dibuat.',
            'data' => [
                'filename' => $filename,
                'mime_type' => 'application/pdf',
                'pdf_base64' => base64_encode($pdf->output()),
            ],
        ]);
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
            $temp = $tanggalMulai;
            $tanggalMulai = $tanggalSelesai;
            $tanggalSelesai = $temp;
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
