<?php

namespace App\Services;

use App\Models\News;
use App\Models\SatuanKerja;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendingNewsService
{
    /**
     * Get Top 10 National Trending News (Rolling window 7 days)
     */
    public function getNationalTrending()
    {
        return $this->getBaseTrendingQuery()->limit(10)->get();
    }

    /**
     * Get Top 10 Local Trending News based on User's Satuan Kerja and sub-units
     */
    public function getLocalTrending(User $user)
    {
        if (!$user->satuan_kerja_id) {
            return collect(); // User has no satuan kerja
        }

        $satuanKerjaIds = $this->getSatuanKerjaAndDescendants($user->satuan_kerja_id);

        return $this->getBaseTrendingQuery()
            ->whereIn('news.satuan_kerja_id', $satuanKerjaIds)
            ->limit(10)
            ->get();
    }

    /**
     * Base query for aggregating views over the last 7 days
     */
    private function getBaseTrendingQuery()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();

        return News::select(
                'news.id',
                'news.judul as title',
                'news.satuan_kerja_id',
                'satuan_kerjas.nama_satuan_kerja',
                DB::raw('COALESCE(SUM(news_daily_views.views_count), 0) as total_views')
            )
            ->join('satuan_kerjas', 'news.satuan_kerja_id', '=', 'satuan_kerjas.id')
            ->join('news_daily_views', 'news.id', '=', 'news_daily_views.news_id')
            ->where('news_daily_views.view_date', '>=', $sevenDaysAgo)
            ->where('news.status', 'PUBLISHED')
            ->whereNull('news.deleted_at')
            ->groupBy('news.id', 'news.judul', 'news.satuan_kerja_id', 'satuan_kerjas.nama_satuan_kerja')
            ->orderByDesc('total_views');
    }

    /**
     * Recursively get a Satuan Kerja ID and all its descendants' IDs.
     */
    private function getSatuanKerjaAndDescendants($id)
    {
        $ids = [$id];
        
        // Find children
        $children = SatuanKerja::where('parent_id', $id)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getSatuanKerjaAndDescendants($childId));
        }

        return $ids;
    }
}
