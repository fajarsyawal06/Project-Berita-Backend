<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\PointConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointConfigurationController extends Controller
{
    public function index()
    {
        $data = PointConfiguration::orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $config = PointConfiguration::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $config]);
    }

    public function update(Request $request, $id)
    {
        $config = PointConfiguration::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'poin' => 'required|integer',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Kita hanya mengizinkan update poin, deskripsi, dan status_aktif.
        // jenis_aktivitas tidak boleh diubah agar tidak merusak relasi hardcode di backend.
        $config->update($request->only(['poin', 'deskripsi', 'status_aktif']));

        return response()->json(['success' => true, 'message' => 'Konfigurasi Poin berhasil diperbarui', 'data' => $config]);
    }
}
