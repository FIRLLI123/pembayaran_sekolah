<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\TagihanResource;
use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    private $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    public function index(Request $request): JsonResponse
    {
        info('Tagihan index request parameters: ', $request->all());
        $filters = [
            'siswa_id' => $request->get('siswa_id'),
            'kelas_id' => $request->get('kelas_id'),
        ];

        $query = Tagihan::with(['siswa.kelas'])
            ->filter($filters);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        $query->select(
                'siswa_id',
                DB::raw('SUM(nominal_tagihan) as total_nominal'),
                DB::raw('SUM(sisa_tagihan) as total_sisa')
            )
            ->groupBy('siswa_id');

        $perPage = (int) $request->get('per_page', 10);
        $tagihan = $query->paginate($perPage);

        $data = collect($tagihan->items())->map(function ($item) {
            return [
                'siswa' => [
                    'id' => $item->siswa->id,
                    'nis' => $item->siswa->nis,
                    'nama_siswa' => $item->siswa->nama_siswa,
                    'kelas' => $item->siswa->kelas ? [
                        'id' => $item->siswa->kelas->id,
                        'nama_kelas' => $item->siswa->kelas->nama_kelas,
                    ] : null,
                ],
                'total_nominal' => (int) $item->total_nominal,
                'total_sisa' => (int) $item->total_sisa,
            ];
        })->values();

        return response()->json([
            'message' => 'Data tagihan berhasil diambil.',
            'data' => $data,
            'meta' => [
                'current_page' => $tagihan->currentPage(),
                'last_page' => $tagihan->lastPage(),
                'per_page' => $tagihan->perPage(),
                'total' => $tagihan->total(),
            ],
            'filter_options' => [
                'siswa' => Siswa::with('kelas')->orderBy('nama_siswa')->get(['id', 'nama_siswa', 'nis', 'kelas_id']),
                'kelas' => Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']),
                'jenis_pembayaran' => JenisPembayaran::orderBy('nama_pembayaran')->get(['id', 'nama_pembayaran']),
            ],
        ]);
    }

    public function detail(Request $request, Siswa $siswa): JsonResponse
    {
        $query = Tagihan::with(['jenisPembayaran', 'siswa'])
            ->where('siswa_id', $siswa->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bulan')) {
            $query->where('periode_bulan', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->where('periode_tahun', $request->tahun);
        }

        $detail = $query->orderBy('periode_tahun')
            ->orderBy('periode_bulan')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Detail tagihan siswa berhasil diambil.',
            'data' => TagihanResource::collection($detail->items()),
            'meta' => [
                'current_page' => $detail->currentPage(),
                'last_page' => $detail->lastPage(),
                'per_page' => $detail->perPage(),
                'total' => $detail->total(),
            ],
            'siswa' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nama_siswa' => $siswa->nama_siswa,
            ],
        ]);
    }

    public function totalBelumLunas(Siswa $siswa): JsonResponse
    {
        $total = Tagihan::where('siswa_id', $siswa->id)
            ->where('status', '!=', 'lunas')
            ->sum('sisa_tagihan');

        return response()->json([
            'message' => 'Total tagihan belum lunas berhasil diambil.',
            'data' => [
                'siswa_id' => $siswa->id,
                'total' => (int) $total,
            ],
        ]);
    }

    public function statusBulan($tahun): JsonResponse
    {
        return response()->json([
            'message' => 'Status bulan berhasil diambil.',
            'data' => $this->tagihanService->statusBulan((int) $tahun),
        ]);
    }

    public function generateSpp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bulan' => 'required|array|min:1',
            'bulan.*' => 'integer|between:1,12',
            'tahun' => 'required|integer',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'siswa_id' => 'nullable|integer|exists:siswa,id',
            'nominal_custom' => 'nullable|numeric|min:1',
        ]);

        try {
            $result = $this->tagihanService->generateSpp($validated);

            return response()->json([
                'message' => 'Generate tagihan SPP selesai diproses.',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function generateCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jenis_pembayaran_id' => 'required|exists:jenis_pembayaran,id',
            'siswa_id' => 'required|array|min:1',
            'siswa_id.*' => 'integer|exists:siswa,id',
            'jatuh_tempo' => 'nullable|date',
            'nominal_custom' => 'nullable|numeric|min:1',
            'force' => 'nullable|boolean',
        ]);

        $result = $this->tagihanService->generateCustom($validated);
        $status = !empty($result['requires_confirmation']) ? 409 : 200;

        return response()->json([
            'message' => $result['message'],
            'data' => $result,
        ], $status);
    }

    public function deleteGenerated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bulan' => 'required|array|min:1',
            'bulan.*' => 'integer|between:1,12',
            'tahun' => 'required|integer',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'siswa_id' => 'nullable|integer|exists:siswa,id',
            'jenis_pembayaran_id' => 'nullable|integer|exists:jenis_pembayaran,id',
        ]);

        try {
            $deleted = $this->tagihanService->deleteGenerated($validated);

            return response()->json([
                'message' => 'Berhasil menghapus tagihan generated.',
                'data' => [
                    'deleted' => $deleted,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
