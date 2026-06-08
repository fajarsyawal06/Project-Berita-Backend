<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = $request->user();

        // Jika belum login
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Cek jika user punya minimal salah satu dari permission yang dibutuhkan
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'Forbidden: You do not have the required permission(s) [' . implode(', ', $permissions) . '] to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
