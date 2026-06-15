<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\KelasResource;
use App\Models\Kelas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Kelas::query()->withCount(['siswa as jumlah_siswa']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', '%' . $search . '%')
                    ->orWhere('tahun_ajaran', 'like', '%' . $search . '%');
            });
        }

        $kelas = $query->orderBy('nama_kelas')->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data kelas berhasil diambil.',
            'data' => KelasResource::collection($kelas->items()),
            'meta' => [
                'current_page' => $kelas->currentPage(),
                'last_page' => $kelas->lastPage(),
                'per_page' => $kelas->perPage(),
                'total' => $kelas->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:50',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $validated['nama_kelas'],
            'tahun_ajaran' => $validated['tahun_ajaran'],
            'created_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data kelas berhasil ditambahkan.',
            'data' => new KelasResource($kelas),
        ], 201);
    }

    public function show(Kelas $kela): JsonResponse
    {
        $kela->loadCount(['siswa as jumlah_siswa']);

        return response()->json([
            'message' => 'Detail kelas berhasil diambil.',
            'data' => new KelasResource($kela),
        ]);
    }

    public function update(Request $request, Kelas $kela): JsonResponse
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:50',
        ]);

        $kela->update([
            'nama_kelas' => $validated['nama_kelas'],
            'tahun_ajaran' => $validated['tahun_ajaran'],
            'updated_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data kelas berhasil diupdate.',
            'data' => new KelasResource($kela->fresh()->loadCount(['siswa as jumlah_siswa'])),
        ]);
    }

    public function destroy(Kelas $kela): JsonResponse
    {
        $kela->delete();

        return response()->json([
            'message' => 'Data kelas berhasil dihapus.',
        ]);
    }
}
