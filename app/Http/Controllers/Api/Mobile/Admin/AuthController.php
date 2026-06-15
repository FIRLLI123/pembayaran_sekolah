<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'petugas'], true)) {
            Auth::logout();

            return response()->json([
                'message' => 'Role akun ini tidak diizinkan mengakses aplikasi admin mobile.',
            ], 403);
        }

        $tokenName = $credentials['device_name'] ?? 'android-admin-app';
        $abilities = $user->role === 'admin'
            ? ['mobile:admin', 'mobile:petugas']
            : ['mobile:petugas'];

        $token = $user->createToken($tokenName, $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'siswa_id' => $user->siswa_id,
                    'ortu_id' => $user->ortu_id,
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Data profil berhasil diambil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'siswa_id' => $user->siswa_id,
                    'ortu_id' => $user->ortu_id,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user ? $user->currentAccessToken() : null;

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
