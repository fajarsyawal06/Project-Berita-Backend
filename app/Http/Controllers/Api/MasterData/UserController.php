<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserCredentialsEmail;

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
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userData = $request->except(['avatar']);
        
        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Set password bawaan jika tidak diisi
        $rawPassword = $request->password ?? Str::random(8);
        $userData['password'] = Hash::make($rawPassword);

        $user = User::create($userData);

        // Kirim email notifikasi
        try {
            Mail::to($user->email)->send(new UserCredentialsEmail($user, $rawPassword));
        } catch (\Exception $e) {
            // Log error if email fails
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email kredensial: ' . $e->getMessage());
        }

        // Load relasi agar responsenya lengkap
        $user->load(['role', 'jabatan', 'satuanKerja']);

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan', 'data' => $user], 201);
    }

    public function show($id)
    {
        $user = User::with(['role', 'jabatan', 'satuanKerja'])
                    ->withCount('news as jumlah_berita')
                    ->find($id);
                    
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan'], 404);
        }
        
        $user->total_poin = $user->pointHistories()->sum('jumlah_poin');
        $user->durasi_online_menit = $user->total_online_minutes ?? 0;
        
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
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userData = $request->except(['password', 'avatar']); // Jangan sembarangan update password via form profil biasa
        
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        
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
