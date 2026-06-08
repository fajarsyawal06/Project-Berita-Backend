<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\TutorialCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTutorialCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TutorialCategory::withCount('videos')->orderBy('urutan_tampilan')->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:tutorial_categories',
            'deskripsi' => 'nullable|string',
            'ikon' => 'nullable|string|max:50',
            'urutan_tampilan' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = TutorialCategory::create([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
                'ikon' => $request->ikon,
                'urutan_tampilan' => $request->urutan_tampilan ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = TutorialCategory::withCount('videos')->find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = TutorialCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:tutorial_categories,nama_kategori,' . $id,
            'deskripsi' => 'nullable|string',
            'ikon' => 'nullable|string|max:50',
            'urutan_tampilan' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
                'ikon' => $request->ikon,
                'urutan_tampilan' => $request->urutan_tampilan ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $category = TutorialCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }

        $videoCount = $category->videos()->count();

        // Jika kategori punya video dan tidak ada reassign_to yang diberikan, kembalikan 409
        if ($videoCount > 0 && !$request->has('reassign_to')) {
            return response()->json([
                'success' => false,
                'message' => "Kategori ini memiliki $videoCount video. Pilih tindakan: Reassign atau Batalkan.",
                'video_count' => $videoCount,
                'requires_reassign' => true
            ], 409);
        }

        try {
            // Jika ada reassign_to, pindahkan video ke kategori tersebut
            if ($videoCount > 0 && $request->has('reassign_to')) {
                $reassignToId = $request->input('reassign_to');
                
                // Validasi bahwa kategori tujuan ada dan bukan kategori yang sedang dihapus
                $targetCategory = TutorialCategory::find($reassignToId);
                if (!$targetCategory || $targetCategory->id == $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kategori tujuan reassign tidak valid.'
                    ], 422);
                }

                $category->videos()->update(['tutorial_category_id' => $reassignToId]);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
