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
        $totalViews = TutorialVideoView::count();
        $totalWatchTimeSeconds = TutorialVideoView::sum('watch_time_seconds');
        
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
        $topVideos = TutorialVideo::withCount('views')
            ->orderByDesc('views_count')
            ->take(10)
            ->get();

        $top1 = $topVideos->first();

        return response()->json([
            'success' => true,
            'data' => [
                'top_1' => $top1,
                'top_10' => $topVideos
            ]
        ]);
    }

    public function getLatestVideo()
    {
        $latest = TutorialVideo::withCount('views')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $latest
        ]);
    }

    public function getChartData()
    {
        // Sebaran Penonton berdasarkan Satuan Kerja (Pie/Donut Chart)
        $viewsBySatuanKerja = DB::table('tutorial_video_views')
            ->join('users', 'tutorial_video_views.user_id', '=', 'users.id')
            ->join('satuan_kerjas', 'users.satuan_kerja_id', '=', 'satuan_kerjas.id')
            ->select('satuan_kerjas.nama_satuan_kerja as name', DB::raw('count(tutorial_video_views.id) as value'))
            ->groupBy('satuan_kerjas.id', 'satuan_kerjas.nama_satuan_kerja')
            ->get();

        // Sebaran Penonton berdasarkan Role (Bar Chart)
        $viewsByRole = DB::table('tutorial_video_views')
            ->join('users', 'tutorial_video_views.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.nama_role as name', DB::raw('count(tutorial_video_views.id) as value'))
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
        $logs = TutorialVideoView::with(['user:id,nama_lengkap,satuan_kerja_id', 'user.satuanKerja:id,nama_satuan_kerja', 'video:id,judul'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
