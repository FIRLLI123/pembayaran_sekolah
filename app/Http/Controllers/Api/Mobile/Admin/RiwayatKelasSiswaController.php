<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\RiwayatKelasSiswaResource;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatKelasSiswaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RiwayatKelasSiswa::with(['siswa', 'kelasLama', 'kelasBaru']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        $riwayat = $query->orderByDesc('tanggal_pindah')
            ->orderByDesc('id')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data riwayat siswa berhasil diambil.',
            'data' => RiwayatKelasSiswaResource::collection($riwayat->items()),
            'meta' => [
                'current_page' => $riwayat->currentPage(),
                'last_page' => $riwayat->lastPage(),
                'per_page' => $riwayat->perPage(),
                'total' => $riwayat->total(),
            ],
        ]);
    }

    public function generateKenaikan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_baru_id' => 'required|exists:kelas,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'required|integer|exists:siswa,id',
        ]);

        $kelasBaruId = (int) $validated['kelas_baru_id'];
        $siswaTerpilih = Siswa::whereIn('id', $validated['siswa_ids'])->get(['id', 'kelas_id']);

        $siswaNaikKelas = $siswaTerpilih->filter(function ($item) use ($kelasBaruId) {
            return (int) $item->kelas_id !== $kelasBaruId;
        });

        if ($siswaNaikKelas->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada siswa yang diproses karena semua sudah berada di kelas tujuan.',
            ], 422);
        }

        $now = now();
        $userId = auth()->id();
        $historyRows = $siswaNaikKelas->map(function ($item) use ($kelasBaruId, $now, $userId) {
            return [
                'siswa_id' => $item->id,
                'kelas_lama_id' => $item->kelas_id,
                'kelas_baru_id' => $kelasBaruId,
                'tanggal_pindah' => $now,
                'created_user' => $userId ? (string) $userId : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();

        DB::transaction(function () use ($historyRows, $kelasBaruId, $siswaNaikKelas, $now, $userId) {
            RiwayatKelasSiswa::insert($historyRows);

            Siswa::whereIn('id', $siswaNaikKelas->pluck('id')->all())
                ->update([
                    'kelas_id' => $kelasBaruId,
                    'updated_user' => $userId,
                    'updated_at' => $now,
                ]);
        });

        return response()->json([
            'message' => 'Kenaikan kelas berhasil diproses.',
            'data' => [
                'processed_count' => $siswaNaikKelas->count(),
                'kelas_baru_id' => $kelasBaruId,
            ],
        ]);
    }
}
