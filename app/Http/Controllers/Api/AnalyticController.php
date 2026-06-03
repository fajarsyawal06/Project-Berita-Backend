<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticController extends Controller
{
    public function getAnalyticsData(Request $request)
    {
        $source = $request->query('source');
        $period = $request->query('period', '30days');
        
        // Di aplikasi asli, kita akan Query Builder/Eloquent ke DB atau View.
        // Simulasi data respons dinamis berdasarkan konfigurasi wizard
        
        if ($source === 'v_news_performance') {
            return response()->json([
                'status' => 'success',
                'data' => [
                    ['name' => 'Jan', 'value' => 400],
                    ['name' => 'Feb', 'value' => 300],
                    ['name' => 'Mar', 'value' => 550],
                    ['name' => 'Apr', 'value' => 450],
                    ['name' => 'Mei', 'value' => 600],
                    ['name' => 'Jun', 'value' => 700],
                ]
            ]);
        }
        
        if ($source === 'v_news_count_by_category') {
            return response()->json([
                'status' => 'success',
                'data' => [
                    ['name' => 'Politik', 'value' => 120],
                    ['name' => 'Ekonomi', 'value' => 200],
                    ['name' => 'Hiburan', 'value' => 150],
                    ['name' => 'Olahraga', 'value' => 80],
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => rand(1000, 50000),
                'growth' => '+' . rand(1, 20) . '%'
            ]
        ]);
    }
}
