<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input dari React
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Cek Kredensial
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau Password salah.'
            ], 401);
        }

        // 3. Ambil data User beserta relasi Role-nya
        $user = User::with('role.permissions')->where('email', $request->email)->first();

        // 4. Cek apakah akun dinonaktifkan
        if (!$user->status_aktif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda dinonaktifkan. Hubungi Admin.'
            ], 403);
        }

        // 5. Buat Token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Kirim Respons ke Frontend (React)
        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'nama_lengkap' => $user->nama_lengkap,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role ? $user->role->kode_role : null, // Mengirim 'P-01', 'P-04', dll ke React
                'roles' => $user->role ? [['name' => $user->role->nama_role]] : [],
                'permissions' => $user->role && $user->role->permissions ? $user->role->permissions->pluck('name') : [],
                'preferences' => $user->preferences,
                'satuan_kerja' => $user->satuanKerja ? [
                    'id' => $user->satuanKerja->id,
                    'nama_satker' => $user->satuanKerja->nama_satker
                ] : null
            ]
        ], 200);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        $user->load('role.permissions');

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'nama_lengkap' => $user->nama_lengkap,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role ? $user->role->kode_role : null,
                'roles' => $user->role ? [['name' => $user->role->nama_role]] : [],
                'permissions' => $user->role && $user->role->permissions ? $user->role->permissions->pluck('name') : [],
                'preferences' => $user->preferences,
                'satuan_kerja' => $user->satuanKerja ? [
                    'id' => $user->satuanKerja->id,
                    'nama_satker' => $user->satuanKerja->nama_satker
                ] : null
            ]
        ], 200);
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        // Merge existing preferences with new ones
        $currentPreferences = is_array($user->preferences) ? $user->preferences : [];
        $newPreferences = $request->all();
        
        $user->preferences = array_merge($currentPreferences, $newPreferences);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences updated successfully',
            'preferences' => $user->preferences
        ], 200);
    }

    public function ping(Request $request)
    {
        $user = Auth::user();
        
        // Cek jika ada aktivitas sebelumnya
        if ($user->last_active_at) {
            $lastActive = \Carbon\Carbon::parse($user->last_active_at);
            $now = \Carbon\Carbon::now();
            
            $diffInMinutes = $lastActive->diffInMinutes($now);
            
            // Jika rentang waktu wajar (misal kurang dari 5 menit), tambahkan ke total online
            if ($diffInMinutes > 0 && $diffInMinutes <= 5) {
                $user->total_online_minutes = ($user->total_online_minutes ?? 0) + $diffInMinutes;
            }
        }
        
        $user->last_active_at = \Carbon\Carbon::now();
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ping recorded'
        ], 200);
    }
}