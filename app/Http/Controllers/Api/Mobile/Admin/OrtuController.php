<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OrtuResource;
use App\Models\Ortu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrtuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ortu::query()->withCount(['users as jumlah_user']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_ayah', 'like', '%' . $search . '%')
                    ->orWhere('nama_ibu', 'like', '%' . $search . '%')
                    ->orWhere('no_hp', 'like', '%' . $search . '%');
            });
        }

        $ortu = $query->latest()->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data orang tua berhasil diambil.',
            'data' => OrtuResource::collection($ortu->items()),
            'meta' => [
                'current_page' => $ortu->currentPage(),
                'last_page' => $ortu->lastPage(),
                'per_page' => $ortu->perPage(),
                'total' => $ortu->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $ortu = Ortu::create($validated);

        return response()->json([
            'message' => 'Data orang tua berhasil ditambahkan.',
            'data' => new OrtuResource($ortu->fresh()->loadCount(['users as jumlah_user'])),
        ], 201);
    }

    public function show(Ortu $ortu): JsonResponse
    {
        return response()->json([
            'message' => 'Detail orang tua berhasil diambil.',
            'data' => new OrtuResource($ortu->loadCount(['users as jumlah_user'])),
        ]);
    }

    public function update(Request $request, Ortu $ortu): JsonResponse
    {
        $validated = $request->validate([
            'nama_ayah' => 'required|string|max:255',
            'nama_ibu' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $ortu->update($validated);

        return response()->json([
            'message' => 'Data orang tua berhasil diupdate.',
            'data' => new OrtuResource($ortu->fresh()->loadCount(['users as jumlah_user'])),
        ]);
    }

    public function destroy(Ortu $ortu): JsonResponse
    {
        $ortu->delete();

        return response()->json([
            'message' => 'Data orang tua berhasil dihapus.',
        ]);
    }
}
