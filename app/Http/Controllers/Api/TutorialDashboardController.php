<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TutorialVideo;
use App\Models\TutorialVideoView;
use App\Models\TutorialVideoComment;
use Illuminate\Support\Facades\DB;

class TutorialDashboardController extends Controller
{
    public function getKpiSummary()
    {
        // Total Views (Unique User per Video)
        $totalViewsQuery = DB::table('tutorial_video_interactions')
            ->select('user_id', 'tutorial_video_id')
            ->distinct()
            ->get();
        $totalViews = $totalViewsQuery->count();

        // Total Watch Time (Sum of Max Position per User per Video)
        $maxPositions = DB::table('tutorial_video_interactions')
            ->selectRaw('MAX(position_seconds) as max_position')
            ->groupBy('user_id', 'tutorial_video_id')
            ->get();
        $totalWatchTimeSeconds = $maxPositions->sum('max_position');
        
        $hours = floor($totalWatchTimeSeconds / 3600);
        $minutes = floor(($totalWatchTimeSeconds / 60) % 60);
        $formattedWatchTime = "{$hours} jam {$minutes} menit";

        $totalVideo = TutorialVideo::count();
        
        // Count unique categories that have videos
        $totalKategori = TutorialVideo::distinct('tutorial_category_id')->count('tutorial_category_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total_views' => $totalViews,
                'watch_time' => $formattedWatchTime,
                'total_video' => $totalVideo,
                'total_kategori' => $totalKategori,
            ]
        ]);
    }

    public function getTopVideos()
    {
        $topVideosWithCount = DB::table('tutorial_video_interactions')
            ->select('tutorial_video_id', DB::raw('count(distinct user_id) as views_count'))
            ->groupBy('tutorial_video_id')
            ->orderByDesc('views_count')
            ->take(10)
            ->get();
            
        $topVideoModels = collect();
        foreach($topVideosWithCount as $tv) {
             $video = TutorialVideo::find($tv->tutorial_video_id);
             if ($video) {
                 $video->views_count = $tv->views_count;
                 $topVideoModels->push($video);
             }
        }

        $top1 = $topVideoModels->first();

        return response()->json([
            'success' => true,
            'data' => [
                'top_1' => $top1,
                'top_10' => $topVideoModels
            ]
        ]);
    }

    public function getLatestVideo()
    {
        $latest = TutorialVideo::latest()->first();
        if ($latest) {
             $viewsCount = DB::table('tutorial_video_interactions')
                 ->where('tutorial_video_id', $latest->id)
                 ->distinct('user_id')
                 ->count('user_id');
             $latest->views_count = $viewsCount;
        }

        return response()->json([
            'success' => true,
            'data' => $latest
        ]);
    }

    public function getChartData()
    {
        // Sebaran Penonton berdasarkan Satuan Kerja (Pie/Donut Chart)
        $viewsBySatuanKerja = DB::table('tutorial_video_interactions')
            ->join('users', 'tutorial_video_interactions.user_id', '=', 'users.id')
            ->join('satuan_kerjas', 'users.satuan_kerja_id', '=', 'satuan_kerjas.id')
            ->select('satuan_kerjas.nama_satuan_kerja as name', DB::raw('count(distinct users.id) as value'))
            ->groupBy('satuan_kerjas.id', 'satuan_kerjas.nama_satuan_kerja')
            ->get();

        // Sebaran Penonton berdasarkan Role (Bar Chart)
        $viewsByRole = DB::table('tutorial_video_interactions')
            ->join('users', 'tutorial_video_interactions.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.nama_role as name', DB::raw('count(distinct users.id) as value'))
            ->groupBy('roles.id', 'roles.nama_role')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'satuan_kerja' => $viewsBySatuanKerja,
                'role' => $viewsByRole
            ]
        ]);
    }

    public function getComments(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $comments = TutorialVideoComment::with(['user:id,nama_lengkap,avatar', 'video:id,judul'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    public function getInteractionLogs(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $logs = \App\Models\TutorialVideoInteraction::with(['user:id,nama_lengkap,satuan_kerja_id', 'user.satuanKerja:id,nama_satuan_kerja', 'video:id,judul'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
