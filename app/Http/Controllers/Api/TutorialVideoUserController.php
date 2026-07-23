<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TutorialVideo;
use App\Models\TutorialCategory;
use App\Models\TutorialVideoComment;

class TutorialVideoUserController extends Controller
{
    /**
     * Display a listing of the resource filtered by user's role.
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        $roleId = $user ? $user->role_id : 'P-05';

        $query = TutorialVideo::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role_id', $roleId);
        })->with('category');

        // Optional filtering by category
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('tutorial_category_id', $request->category_id);
        }

        // Optional search by title
        if ($request->has('search') && $request->search != '') {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $videos = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth('api')->user();
        $roleId = $user ? $user->role_id : 'P-05';

        $video = TutorialVideo::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role_id', $roleId);
        })->with('category')->find($id);

        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $video
        ]);
    }

    /**
     * Display a listing of tutorial categories.
     */
    public function getCategories()
    {
        $categories = TutorialCategory::all();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a new comment/feedback for a tutorial video.
     */
    public function storeComment(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $roleName = $user->role->nama_role ?? '';
        $roleCode = $user->role->kode_role ?? $user->role_id;

        if (in_array($roleName, ['Viewer', 'Viewer Umum']) || $roleCode === 'P-05') {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk memberikan saran.'], 403);
        }

        $request->validate([
            'komentar' => 'required|string|max:1000'
        ]);

        $video = TutorialVideo::find($id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan.'], 404);
        }

        $comment = TutorialVideoComment::create([
            'user_id' => $user->id,
            'tutorial_video_id' => $video->id,
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Saran berhasil dikirim.',
            'data' => $comment
        ]);
    }

    /**
     * Store a new interaction event for a tutorial video.
     */
    public function storeInteraction(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'event_type' => 'required|in:play,pause,seek,complete',
            'position_seconds' => 'required|numeric'
        ]);

        $video = TutorialVideo::find($id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video tidak ditemukan.'], 404);
        }

        \Illuminate\Support\Facades\DB::table('tutorial_video_interactions')->insert([
            'user_id' => $user->id,
            'tutorial_video_id' => $video->id,
            'event_type' => $request->event_type,
            'position_seconds' => (int)$request->position_seconds,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interaksi berhasil dicatat.'
        ]);
    }
}
