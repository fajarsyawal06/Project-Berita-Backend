<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = Auth::user();
        
        // Eager load role just in case it's not loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        $userRole = $user->role->kode_role ?? null;

        // Check if the user's role is in the array of allowed roles
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Anda tidak memiliki izin (role) yang diperlukan untuk tindakan ini.'
            ], 403);
        }

        return $next($request);
    }
}
