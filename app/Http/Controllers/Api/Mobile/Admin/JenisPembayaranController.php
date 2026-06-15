<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\JenisPembayaranResource;
use App\Models\JenisPembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenisPembayaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JenisPembayaran::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_pembayaran', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        $data = $query->orderBy('nama_pembayaran')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data jenis pembayaran berhasil diambil.',
            'data' => JenisPembayaranResource::collection($data->items()),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'nominal_default' => 'required|numeric|min:0',
            'tipe' => 'required|in:rutin,insidental',
            'periode' => 'nullable|in:bulanan,tahunan',
            'keterangan' => 'nullable|string',
        ]);

        if ($validated['tipe'] !== 'rutin') {
            $validated['periode'] = null;
        }

        $jenisPembayaran = JenisPembayaran::create([
            'nama_pembayaran' => $validated['nama_pembayaran'],
            'nominal_default' => $validated['nominal_default'],
            'tipe' => $validated['tipe'],
            'periode' => isset($validated['periode']) ? $validated['periode'] : null,
            'keterangan' => isset($validated['keterangan']) ? $validated['keterangan'] : null,
            'created_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Jenis pembayaran berhasil ditambahkan.',
            'data' => new JenisPembayaranResource($jenisPembayaran),
        ], 201);
    }

    public function show(JenisPembayaran $jenis_pembayaran): JsonResponse
    {
        return response()->json([
            'message' => 'Detail jenis pembayaran berhasil diambil.',
            'data' => new JenisPembayaranResource($jenis_pembayaran),
        ]);
    }

    public function update(Request $request, JenisPembayaran $jenis_pembayaran): JsonResponse
    {
        $validated = $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'nominal_default' => 'required|numeric|min:0',
            'tipe' => 'required|in:rutin,insidental',
            'periode' => 'nullable|in:bulanan,tahunan',
            'keterangan' => 'nullable|string',
        ]);

        if ($validated['tipe'] !== 'rutin') {
            $validated['periode'] = null;
        }

        $jenis_pembayaran->update([
            'nama_pembayaran' => $validated['nama_pembayaran'],
            'nominal_default' => $validated['nominal_default'],
            'tipe' => $validated['tipe'],
            'periode' => isset($validated['periode']) ? $validated['periode'] : null,
            'keterangan' => isset($validated['keterangan']) ? $validated['keterangan'] : null,
            'updated_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Jenis pembayaran berhasil diupdate.',
            'data' => new JenisPembayaranResource($jenis_pembayaran->fresh()),
        ]);
    }

    public function destroy(JenisPembayaran $jenis_pembayaran): JsonResponse
    {
        $jenis_pembayaran->delete();

        return response()->json([
            'message' => 'Jenis pembayaran berhasil dihapus.',
        ]);
    }
}
