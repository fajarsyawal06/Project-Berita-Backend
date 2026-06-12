<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureToggle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeatureConfigController extends Controller
{
    /**
     * Get all active features for the current user's role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $user ? $user->role_id : null; // Assuming role_id or similar. We will just use 'P-01' etc if provided from frontend.
        
        // Sometimes the frontend might send the role directly if not fully authenticated in some routes,
        // but since we are using sanctum, we can rely on auth user if present.
        // Let's also support an optional 'role' query param for edge cases, or fallback to user role.
        $role = $request->query('role') ?? ($userRole ?? null);

        // Cache the query result for 30 seconds to minimize DB load
        // Cache key includes role to separate role-based toggles
        $cacheKey = 'feature_toggles_active_' . ($role ?? 'guest');
        
        $activeFeatures = Cache::remember($cacheKey, 30, function () use ($role) {
            $toggles = FeatureToggle::currentlyActive()->get();
            $result = [];
            
            foreach ($toggles as $toggle) {
                // If there's a target_role, check if it matches the current user's role
                if (!empty($toggle->target_role)) {
                    // target_role could be multiple roles separated by comma, but based on FR-AD-01 let's assume single role or we can check str_contains
                    $targetRoles = array_map('trim', explode(',', $toggle->target_role));
                    if ($role && in_array($role, $targetRoles)) {
                        $result[$toggle->slug] = true;
                    }
                } else {
                    $result[$toggle->slug] = true;
                }
            }
            
            return $result;
        });

        return response()->json([
            'status' => 'success',
            'data' => $activeFeatures
        ]);
    }
}
