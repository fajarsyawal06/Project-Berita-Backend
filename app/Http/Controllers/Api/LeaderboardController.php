<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index()
    {
        // Menyimpan kueri ke dalam Cache selama 5 menit (300 detik)
        // Sesuai dengan Acceptance Criteria FR-PG-01
       $leaderboard = Cache::remember('leaderboard_top_10', 300, function () {
            
            
            return User::select('id', 'nama_lengkap', 'satuan_kerja_id', 'poin_aktif')
                ->with('satuanKerja') 
                ->orderBy('poin_aktif', 'desc') 
                ->take(10) 
                ->get()
                ->map(function ($user, $index) {
                    return [
                        'peringkat' => $index + 1, 
                        'nama_lengkap' => $user->nama_lengkap,
                        'satuan_kerja' => $user->satuanKerja ? $user->satuanKerja->nama : null,
                        'avatar' => null, // Kita set null dulu sementara waktu
                        'total_poin' => (int) $user->poin_aktif,
                        
                        'is_top_three' => $index < 3, 
                        'highlight_type' => $this->getHighlightType($index)
                    ];
                });
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Top 10 Leaderboard berhasil dimuat',
            'data' => $leaderboard
        ], 200);
    }

    /**
     * Fungsi bantuan untuk menentukan medali Frontend
     */
    private function getHighlightType($index)
    {
        if ($index === 0) return 'gold';   // Peringkat 1
        if ($index === 1) return 'silver'; // Peringkat 2
        if ($index === 2) return 'bronze'; // Peringkat 3
        return 'none';
    }
}