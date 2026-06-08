<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TutorialArticle;
use App\Models\TutorialArticleAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminTutorialArticleController extends Controller
{
    public function index()
    {
        $articles = TutorialArticle::with(['category', 'attachments'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tutorial_category_id' => 'required|exists:tutorial_categories,id',
            'status' => 'required|in:Draft,Published',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240' // max 10MB per file
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $article = TutorialArticle::create([
                'judul' => $request->judul,
                'konten' => $request->konten,
                'tutorial_category_id' => $request->tutorial_category_id,
                'status' => $request->status,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('tutorial-articles/attachments', 'public');
                    TutorialArticleAttachment::create([
                        'tutorial_article_id' => $article->id,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size_bytes' => $file->getSize(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Artikel panduan berhasil ditambahkan',
                'data' => $article->load(['category', 'attachments'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $article = TutorialArticle::with(['category', 'attachments'])->find($id);

        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    public function update(Request $request, $id)
    {
        $article = TutorialArticle::find($id);

        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tutorial_category_id' => 'required|exists:tutorial_categories,id',
            'status' => 'required|in:Draft,Published',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'exists:tutorial_article_attachments,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $article->update([
                'judul' => $request->judul,
                'konten' => $request->konten,
                'tutorial_category_id' => $request->tutorial_category_id,
                'status' => $request->status,
            ]);

            // Hapus attachments yang dipilih
            if ($request->has('delete_attachments')) {
                foreach ($request->delete_attachments as $attachmentId) {
                    $attachment = TutorialArticleAttachment::find($attachmentId);
                    if ($attachment && $attachment->tutorial_article_id == $article->id) {
                        if (Storage::disk('public')->exists($attachment->file_path)) {
                            Storage::disk('public')->delete($attachment->file_path);
                        }
                        $attachment->delete();
                    }
                }
            }

            // Tambah attachments baru
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('tutorial-articles/attachments', 'public');
                    TutorialArticleAttachment::create([
                        'tutorial_article_id' => $article->id,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size_bytes' => $file->getSize(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Artikel panduan berhasil diperbarui',
                'data' => $article->load(['category', 'attachments'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $article = TutorialArticle::find($id);

        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan'], 404);
        }

        try {
            // Hapus file fisik lampiran
            foreach ($article->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Artikel panduan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
