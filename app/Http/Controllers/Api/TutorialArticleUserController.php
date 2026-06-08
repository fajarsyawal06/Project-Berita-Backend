<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TutorialArticle;
use App\Models\TutorialCategory;
use Illuminate\Http\Request;

class TutorialArticleUserController extends Controller
{
    public function index(Request $request)
    {
        $query = TutorialArticle::with(['category'])->where('status', 'Published');

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('tutorial_category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $articles = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    public function show($id)
    {
        $article = TutorialArticle::with(['category', 'attachments'])
                    ->where('status', 'Published')
                    ->find($id);

        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan atau belum dipublikasikan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }
}
