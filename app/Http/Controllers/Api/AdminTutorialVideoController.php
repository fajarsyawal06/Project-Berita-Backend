<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TutorialVideo;
use App\Models\TutorialCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminTutorialVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = TutorialVideo::with(['roles', 'category'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    }

    public function getCategories()
    {
        $categories = TutorialCategory::all();
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
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tutorial_category_id' => 'required|exists:tutorial_categories,id',
            'video' => 'required|mimes:mp4|max:512000', // 500MB
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $videoPath = $request->file('video')->store('tutorial-videos', 'public');
            
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('tutorial-thumbnails', 'public');
            }

            $video = TutorialVideo::create([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tutorial_category_id' => $request->tutorial_category_id,
                'url_video' => $videoPath,
                'thumbnail' => $thumbnailPath,
            ]);

            // Sync roles
            $video->roles()->sync($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Video panduan berhasil diunggah',
                'data' => $video->load('roles')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $video = TutorialVideo::with(['roles', 'category'])->find($id);

        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $video
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $video = TutorialVideo::find($id);

        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tutorial_category_id' => 'required|exists:tutorial_categories,id',
            'video' => 'nullable|mimes:mp4|max:512000',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            if ($request->hasFile('video')) {
                // Delete old video
                if (Storage::disk('public')->exists($video->url_video)) {
                    Storage::disk('public')->delete($video->url_video);
                }
                $video->url_video = $request->file('video')->store('tutorial-videos', 'public');
            }

            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
                $video->thumbnail = $request->file('thumbnail')->store('tutorial-thumbnails', 'public');
            }

            $video->update([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tutorial_category_id' => $request->tutorial_category_id,
            ]);

            // Sync roles
            $video->roles()->sync($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Video panduan berhasil diperbarui',
                'data' => $video->load('roles')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $video = TutorialVideo::find($id);

        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan'], 404);
        }

        try {
            // Detach roles
            $video->roles()->detach();

            // Delete files
            if (Storage::disk('public')->exists($video->url_video)) {
                Storage::disk('public')->delete($video->url_video);
            }
            if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video panduan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus video',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
