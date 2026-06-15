<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['siswa', 'ortu']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'message' => 'Data user berhasil diambil.',
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,ortu',
            'siswa_id' => 'nullable|required_if:role,ortu|exists:siswa,id',
            'ortu_id' => 'nullable|exists:ortu,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'siswa_id' => $validated['role'] === 'ortu' ? (isset($validated['siswa_id']) ? $validated['siswa_id'] : null) : null,
            'ortu_id' => isset($validated['ortu_id']) ? $validated['ortu_id'] : null,
            'created_user' => (string) auth()->id(),
        ]);

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'data' => new UserResource($user->load(['siswa', 'ortu'])),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Detail user berhasil diambil.',
            'data' => new UserResource($user->load(['siswa', 'ortu'])),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,petugas,ortu,viewer',
            'siswa_id' => 'nullable|required_if:role,ortu|exists:siswa,id',
            'ortu_id' => 'nullable|exists:ortu,id',
            'password' => 'nullable|string|min:6',
        ]);

        $role = $validated['role'] === 'viewer' ? 'ortu' : $validated['role'];

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $role,
            'siswa_id' => $role === 'ortu' ? (isset($validated['siswa_id']) ? $validated['siswa_id'] : null) : null,
            'ortu_id' => isset($validated['ortu_id']) ? $validated['ortu_id'] : null,
            'updated_user' => (string) auth()->id(),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User berhasil diupdate.',
            'data' => new UserResource($user->fresh()->load(['siswa', 'ortu'])),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
