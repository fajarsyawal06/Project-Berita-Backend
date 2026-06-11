<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SatuanKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SatuanKerjaController extends Controller
{
    public function index()
    {
        $data = SatuanKerja::with('parent')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_unik' => 'nullable|string|unique:satuan_kerjas,kode_unik',
            'nama_satuan_kerja' => 'required|string|max:255',
            'provinsi_wilayah' => 'nullable|string|max:255',
            'level' => 'nullable|integer',
            'parent_id' => 'nullable|exists:satuan_kerjas,id',
            'status_aktif' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if (empty($data['kode_unik'])) {
            $latest = SatuanKerja::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $data['kode_unik'] = 'SAT-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        // Set default status_aktif to true if not provided
        if (!isset($data['status_aktif'])) {
            $data['status_aktif'] = true;
        }

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $satuanKerja = SatuanKerja::create($data);

        return response()->json(['success' => true, 'message' => 'Satuan Kerja berhasil ditambahkan', 'data' => $satuanKerja], 201);
    }

    public function show($id)
    {
        $satuanKerja = SatuanKerja::find($id);
        if (!$satuanKerja) {
            return response()->json(['success' => false, 'message' => 'Satuan Kerja tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $satuanKerja]);
    }

    public function update(Request $request, $id)
    {
        $satuanKerja = SatuanKerja::find($id);
        if (!$satuanKerja) {
            return response()->json(['success' => false, 'message' => 'Satuan Kerja tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_unik' => 'nullable|string|unique:satuan_kerjas,kode_unik,' . $id,
            'nama_satuan_kerja' => 'required|string|max:255',
            'provinsi_wilayah' => 'nullable|string|max:255',
            'level' => 'nullable|integer',
            'parent_id' => 'nullable|exists:satuan_kerjas,id',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $satuanKerja->update($request->all());

        return response()->json(['success' => true, 'message' => 'Satuan Kerja berhasil diperbarui', 'data' => $satuanKerja]);
    }

    public function destroy($id)
    {
        $satuanKerja = SatuanKerja::find($id);
        if (!$satuanKerja) {
            return response()->json(['success' => false, 'message' => 'Satuan Kerja tidak ditemukan'], 404);
        }

        $satuanKerja->delete();

        return response()->json(['success' => true, 'message' => 'Satuan Kerja berhasil dihapus']);
    }
}
