<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SharedDashboard;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardShareController extends Controller
{
    /**
     * Share the dashboard configuration and generate a token URL.
     */
    public function share(Request $request)
    {
        $request->validate([
            'layouts' => 'required|array',
            'widgets' => 'required|array',
        ]);

        $user = $request->user();

        // Ensure user has permission (P-03 or P-04) - mostly handled by middleware, but good to be safe.
        if (!in_array($user->role->kode_role, ['P-03', 'P-04'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to share dashboard.'
            ], 403);
        }

        // Generate Tokenized unique string (UUIDv4 + hash signature)
        $uuid = Str::uuid()->toString();
        $signature = hash_hmac('sha256', $uuid, config('app.key'));
        $token = $uuid . '.' . $signature;

        $configuration = [
            'dashboard_layout' => $request->layouts,
            'dashboard_widgets' => $request->widgets,
        ];

        $sharedDashboard = SharedDashboard::create([
            'token' => $token,
            'user_id' => $user->id,
            'configuration' => $configuration,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        // "log pembuatan tercatat" - Logging to general app log or SecurityLog if needed
        if (class_exists(\App\Models\SecurityLog::class)) {
            \App\Models\SecurityLog::create([
                'event_type' => 'DASHBOARD_SHARED',
                'table_name' => 'shared_dashboards',
                'description' => "User {$user->name} ({$user->id}) created a shared dashboard link.",
                'created_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard shared successfully.',
            'share_url' => '/shared/d/' . $token,
            'expires_at' => $sharedDashboard->expires_at,
        ]);
    }

    /**
     * Get the shared dashboard configuration.
     */
    public function getSharedDashboard($token)
    {
        $sharedDashboard = SharedDashboard::where('token', $token)->first();

        if (!$sharedDashboard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid share token.'
            ], 404);
        }

        // Verify signature part (optional extra security to ensure it matches the format)
        $parts = explode('.', $token);
        if (count($parts) === 2) {
            $uuid = $parts[0];
            $signature = $parts[1];
            $expectedSignature = hash_hmac('sha256', $uuid, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token signature mismatch.'
                ], 400);
            }
        }

        // Check expiration
        if ($sharedDashboard->expires_at && Carbon::now()->greaterThan($sharedDashboard->expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share link has expired.'
            ], 410);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'owner' => $sharedDashboard->user->nama_lengkap,
                'configuration' => $sharedDashboard->configuration,
                'created_at' => $sharedDashboard->created_at,
            ]
        ]);
    }
}
