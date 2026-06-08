<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TutorialVideo;
use App\Models\TutorialCategory;

class TutorialVideoUserController extends Controller
{
    /**
     * Display a listing of the resource filtered by user's role.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $roleId = $user->role_id;

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
        $user = auth()->user();
        $roleId = $user->role_id;

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
}
