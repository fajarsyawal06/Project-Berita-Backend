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

        // Generate Tokenized unique string (UUIDv4 + hash signature)
        $uuid = Str::uuid()->toString();
        $signature = hash_hmac('sha256', $uuid, config('app.key'));
        $rawToken = $uuid . '.' . $signature;

        // Encrypt with AES-256-GCM (NFR-SEC-02)
        $cipher = 'aes-256-gcm';
        $key = config('app.key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $tag = '';
        $ciphertext = openssl_encrypt($rawToken, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        // Encode to URL-safe base64 string
        $token = rtrim(strtr(base64_encode($iv . $tag . $ciphertext), '+/', '-_'), '=');

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

        // Decode and Decrypt AES-256-GCM
        $decoded = base64_decode(strtr($token, '-_', '+/'));
        $cipher = 'aes-256-gcm';
        $key = config('app.key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $ivlen = openssl_cipher_iv_length($cipher);
        
        if (strlen($decoded) <= $ivlen + 16) {
            return response()->json(['status' => 'error', 'message' => 'Invalid token format.'], 400);
        }

        $iv = substr($decoded, 0, $ivlen);
        $tag = substr($decoded, $ivlen, 16);
        $ciphertext = substr($decoded, $ivlen + 16);

        $rawToken = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($rawToken === false) {
             return response()->json(['status' => 'error', 'message' => 'Token decryption failed.'], 400);
        }

        // Verify signature part
        $parts = explode('.', $rawToken);
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
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid token payload.'], 400);
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
