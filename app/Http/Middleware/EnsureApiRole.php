<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureApiRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Akses ditolak untuk role ini.',
            ], 403);
        }

        return $next($request);
    }
}
