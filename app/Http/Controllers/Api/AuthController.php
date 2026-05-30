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
        $user = User::with('role')->where('email', $request->email)->first();

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
                'email' => $user->email,
                'role' => $user->role->kode_role // Mengirim 'P-01', 'P-04', dll ke React
            ]
        ], 200);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        $user->load('role');

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'email' => $user->email,
                'role' => $user->role->kode_role
            ]
        ], 200);
    }
}