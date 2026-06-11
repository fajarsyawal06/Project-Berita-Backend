<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // Validasi Password saat ini jika ada pengisian password baru
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['current_password' => ['Password saat ini tidak cocok.']]
                ], 422);
            }
            $user->password = Hash::make($request->new_password);
        }

        // Update nama
        $user->nama_lengkap = $request->nama_lengkap;

        // Penanganan upload avatar
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Simpan avatar baru
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();
        $user->load(['role.permissions', 'userPreference', 'satuanKerja']);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.',
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
}
