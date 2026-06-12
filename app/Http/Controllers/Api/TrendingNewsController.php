<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TrendingNewsService;
use Illuminate\Support\Facades\Auth;

class TrendingNewsController extends Controller
{
    protected $trendingService;

    public function __construct(TrendingNewsService $trendingService)
    {
        $this->trendingService = $trendingService;
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'nasional'); // Default to nasional

        if ($tab === 'lokal') {
            // Require authentication for local tab to know the user's Satuan Kerja
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Silakan login untuk melihat trending lokal.'
                ], 401);
            }

            $trendingData = $this->trendingService->getLocalTrending($user);
            $message = 'Top 10 Trending Lokal berhasil diambil.';
        } else {
            // Tab Nasional
            $trendingData = $this->trendingService->getNationalTrending();
            $message = 'Top 10 Trending Nasional berhasil diambil.';
        }

        // Format data to add rank
        $formattedData = $trendingData->map(function ($item, $index) {
            return [
                'peringkat' => $index + 1,
                'judul' => $item->title,
                'satuan_kerja' => $item->nama_satuan_kerja,
                'viewers' => (int) $item->total_views,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $formattedData
        ]);
    }
}
