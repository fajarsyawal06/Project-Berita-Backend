<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserActivity;

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
        $user = User::with(['role.permissions', 'userPreference'])->where('email', $request->email)->first();

        // 4. Cek apakah akun dinonaktifkan
        if (!$user->status_aktif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda dinonaktifkan. Hubungi Admin.'
            ], 403);
        }

        // 5. Buat Token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Catat aktivitas sesi baru (FR-MD-07)
        UserActivity::create([
            'user_id' => $user->id,
            'session_start' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

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
                'preferences' => $user->userPreference ? $user->userPreference->preferences : null,
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
        $user->load(['role.permissions', 'userPreference']);

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
                'preferences' => $user->userPreference ? $user->userPreference->preferences : null,
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
        
        // Dapatkan data preferensi saat ini atau buat yang baru jika belum ada
        $userPreference = UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences' => []]
        );
        
        $currentPreferences = is_array($userPreference->preferences) ? $userPreference->preferences : [];
        $newPreferences = $request->all();
        
        $userPreference->preferences = array_merge($currentPreferences, $newPreferences);
        $userPreference->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences updated successfully',
            'preferences' => $userPreference->preferences
        ], 200);
    }

    public function ping(Request $request)
    {
        $user = Auth::user();
        
        // Cari aktivitas (sesi) terbaru yang session_end-nya kosong atau baru saja diupdate
        $latestActivity = UserActivity::where('user_id', $user->id)
            ->whereNotNull('session_start')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestActivity) {
            // Update session_end
            $latestActivity->session_end = now();
            $latestActivity->save();
        } else {
            // Jika tidak ada (mungkin terhapus), buat record baru agar sesi terekam
            UserActivity::create([
                'user_id' => $user->id,
                'session_start' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_end' => now()
            ]);
        }

        // Opsional: Tetap simpan last_active_at di users jika masih dipakai di fitur lain
        $user->last_active_at = now();
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ping recorded'
        ], 200);
    }
}