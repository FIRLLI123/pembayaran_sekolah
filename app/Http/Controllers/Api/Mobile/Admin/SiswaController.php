<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\SiswaResource;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Siswa::with('kelas');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswa = $query
            ->orderBy('nama_siswa')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data siswa berhasil diambil.',
            'data' => SiswaResource::collection($siswa->items()),
            'meta' => [
                'current_page' => $siswa->currentPage(),
                'last_page' => $siswa->lastPage(),
                'per_page' => $siswa->perPage(),
                'total' => $siswa->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis',
            'nama_siswa' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'upload_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('upload_foto')) {
            $fotoPath = $request->file('upload_foto')->store('foto_siswa', 'public');
        }

        $siswa = Siswa::create([
            'nis' => $validated['nis'],
            'nama_siswa' => $validated['nama_siswa'],
            'kelas_id' => $validated['kelas_id'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat' => $validated['alamat'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'upload_foto' => $fotoPath,
            'created_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data siswa berhasil ditambahkan.',
            'data' => new SiswaResource($siswa->load('kelas')),
        ], 201);
    }

    public function show(Siswa $siswa): JsonResponse
    {
        return response()->json([
            'message' => 'Detail siswa berhasil diambil.',
            'data' => new SiswaResource($siswa->load('kelas')),
        ]);
    }

    public function update(Request $request, Siswa $siswa): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:siswa,nis,' . $siswa->id,
            'nama_siswa' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'upload_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoPath = $siswa->upload_foto;
        if ($request->hasFile('upload_foto')) {
            if ($siswa->upload_foto) {
                Storage::disk('public')->delete($siswa->upload_foto);
            }
            $fotoPath = $request->file('upload_foto')->store('foto_siswa', 'public');
        }

        $siswa->update([
            'nis' => $validated['nis'],
            'nama_siswa' => $validated['nama_siswa'],
            'kelas_id' => $validated['kelas_id'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat' => $validated['alamat'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'upload_foto' => $fotoPath,
            'updated_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'Data siswa berhasil diupdate.',
            'data' => new SiswaResource($siswa->fresh()->load('kelas')),
        ]);
    }

    public function destroy(Siswa $siswa): JsonResponse
    {
        if ($siswa->upload_foto) {
            Storage::disk('public')->delete($siswa->upload_foto);
        }

        $siswa->delete();

        return response()->json([
            'message' => 'Data siswa berhasil dihapus.',
        ]);
    }
}
