<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $data = NewsCategory::all();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_kategori' => 'nullable|string|unique:news_categories,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'warna_badge' => 'nullable|string|max:50',
            'ikon' => 'nullable|string|max:50',
            'status_aktif' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if (empty($data['kode_kategori'])) {
            $latest = NewsCategory::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $data['kode_kategori'] = 'KTG-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        $category = NewsCategory::create($data);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = NewsCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = NewsCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_kategori' => 'nullable|string|unique:news_categories,kode_kategori,' . $id,
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'warna_badge' => 'nullable|string|max:50',
            'ikon' => 'nullable|string|max:50',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $category->update($request->all());

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui', 'data' => $category]);
    }

    public function destroy($id)
    {
        $category = NewsCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }

    public function listActive()
    {
        $data = NewsCategory::where('status_aktif', true)->orderBy('urutan_tampilan', 'asc')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }
}
