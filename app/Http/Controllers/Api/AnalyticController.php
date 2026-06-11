<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    public function getAnalyticsData(Request $request)
    {
        $source = $request->query('dataSource'); // Sesuai config frontend
        $period = $request->query('period', 'monthly');
        $satker = $request->query('satker');
        
        $query = News::query();
        
        // Filter Satuan Kerja secara opsional jika satker terisi
        if (!empty($satker)) {
            $query->whereHas('satuanKerja', function($q) use($satker) {
                $q->where('nama_satuan_kerja', $satker);
            });
        }

        if ($source === 'v_news_performance') {
            // Group berdasarkan tanggal/minggu/bulan
            if ($period === 'daily') {
                $query->select(DB::raw('DATE_FORMAT(created_at, "%d %b") as name'), DB::raw('SUM(views_count) as value'))
                      ->where('created_at', '>=', Carbon::now()->subDays(7));
            } else if ($period === 'weekly') {
                $query->select(DB::raw('CONCAT("Week ", WEEK(created_at)) as name'), DB::raw('SUM(views_count) as value'))
                      ->where('created_at', '>=', Carbon::now()->subWeeks(4));
            } else { // monthly
                $query->select(DB::raw('DATE_FORMAT(created_at, "%b %Y") as name'), DB::raw('SUM(views_count) as value'))
                      ->where('created_at', '>=', Carbon::now()->subMonths(6));
            }

            $data = $query->groupBy('name')->orderBy(DB::raw('MIN(created_at)'))->get();

            // Tidak perlu memberikan data dummy jika database kosong, biarkan UI frontend menangani status kosong
            if ($data->isEmpty()) {
                $data = collect([]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }
        
        if ($source === 'v_news_count_by_category') {
            $data = $query->join('news_categories', 'news.category_id', '=', 'news_categories.id')
                          ->select('news_categories.nama_kategori as name', DB::raw('COUNT(news.id) as value'))
                          ->groupBy('news_categories.nama_kategori')
                          ->get();
                          
            // Tidak perlu fallback data dummy
            if ($data->isEmpty()) {
                $data = collect([]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        // Scorecard (Summary Metrics)
        $totalNews = News::count();
        $lastMonthCount = News::where('created_at', '>=', Carbon::now()->subMonth())->count();
        
        // Asumsi pertumbuhan dihitung sederhana sebagai persentase dari total
        // Pada aplikasi nyata, akan dihitung dari perbandingan bulan ke bulan
        $growth = $lastMonthCount > 0 ? '+' . round(($lastMonthCount / max(1, $totalNews - $lastMonthCount)) * 100) . '%' : '0%';

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $totalNews,
                'growth' => $growth
            ]
        ]);
    }
}
