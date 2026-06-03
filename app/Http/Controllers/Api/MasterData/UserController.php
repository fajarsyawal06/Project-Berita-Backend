<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Memuat data dengan relasinya agar bisa ditampilkan dengan baik di frontend
        $data = User::with(['role', 'jabatan', 'satuanKerja'])->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip_pegawai' => 'required|string|unique:users,nip_pegawai',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'satuan_kerja_id' => 'required|exists:satuan_kerjas,id',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userData = $request->all();
        // Set password bawaan jika tidak diisi
        $userData['password'] = Hash::make($request->password ?? 'password123');

        $user = User::create($userData);

        // Load relasi agar responsenya lengkap
        $user->load(['role', 'jabatan', 'satuanKerja']);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan', 'data' => $user], 201);
    }

    public function show($id)
    {
        $user = User::with(['role', 'jabatan', 'satuanKerja'])->find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nip_pegawai' => 'required|string|unique:users,nip_pegawai,' . $id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'satuan_kerja_id' => 'required|exists:satuan_kerjas,id',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userData = $request->except(['password']); // Jangan sembarangan update password via form profil biasa
        
        // Update password jika ada inputnya
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        $user->load(['role', 'jabatan', 'satuanKerja']);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil diperbarui', 'data' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
    }
}
