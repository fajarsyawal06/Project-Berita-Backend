<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JabatanController extends Controller
{
    public function index()
    {
        $data = Jabatan::with('satuanKerja')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_jabatan' => 'nullable|string|unique:jabatans,kode_jabatan',
            'nama_jabatan' => 'required|string|max:255',
            'level_hierarki' => 'nullable|integer',
            'satuan_kerja_id' => 'nullable|exists:satuan_kerjas,id',
            'deskripsi' => 'nullable|string',
        ]);

        $data = $request->all();
        if (empty($data['kode_jabatan'])) {
            $latest = Jabatan::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $data['kode_jabatan'] = 'JAB-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        $jabatan = Jabatan::create($data);

        return response()->json(['success' => true, 'message' => 'Jabatan berhasil ditambahkan', 'data' => $jabatan], 201);
    }

    public function show($id)
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $jabatan]);
    }

    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_jabatan' => 'nullable|string|unique:jabatans,kode_jabatan,' . $id,
            'nama_jabatan' => 'required|string|max:255',
            'level_hierarki' => 'nullable|integer',
            'satuan_kerja_id' => 'nullable|exists:satuan_kerjas,id',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $jabatan->update($request->all());

        return response()->json(['success' => true, 'message' => 'Jabatan berhasil diperbarui', 'data' => $jabatan]);
    }

    public function destroy($id)
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], 404);
        }

        $jabatan->delete();

        return response()->json(['success' => true, 'message' => 'Jabatan berhasil dihapus']);
    }
}
