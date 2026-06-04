<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $roles = $request->input('roles'); // array of role names
        $tahun = $request->input('tahun', date('Y'));
        $semester = $request->input('semester');

        // Dynamic cache key based on filters
        $cacheKey = 'leaderboard_top_10_' . md5(json_encode([$roles, $tahun, $semester]));

        // Menyimpan kueri ke dalam Cache selama 5 menit (300 detik)
        // Sesuai dengan Acceptance Criteria FR-PG-01
        $leaderboard = Cache::remember($cacheKey, 300, function () use ($roles, $tahun, $semester) {
            
            // Tentukan rentang tanggal
            $startDate = Carbon::create($tahun, 1, 1)->startOfDay();
            $endDate = Carbon::create($tahun, 12, 31)->endOfDay();
            
            if ($semester === 'Semester 1') {
                $endDate = Carbon::create($tahun, 6, 30)->endOfDay();
            } elseif ($semester === 'Semester 2') {
                $startDate = Carbon::create($tahun, 7, 1)->startOfDay();
            }

            return User::select('id', 'nama_lengkap', 'satuan_kerja_id', 'poin_aktif', 'role_id')
                ->when($roles, function ($query) use ($roles) {
                    $query->whereHas('role', function ($q) use ($roles) {
                        $q->whereIn('nama_role', (array) $roles);
                    });
                })
                ->withSum(['pointHistories as total_poin_periode' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }], 'jumlah_poin')
                ->with('satuanKerja') 
                ->orderByRaw('COALESCE(total_poin_periode, 0) DESC') 
                ->orderBy('poin_aktif', 'desc') // fallback secondary sort if points are equal
                ->take(10) 
                ->get()
                ->map(function ($user, $index) {
                    return [
                        'peringkat' => $index + 1, 
                        'nama_lengkap' => $user->nama_lengkap,
                        'satuan_kerja' => $user->satuanKerja ? $user->satuanKerja->nama : null,
                        'avatar' => null, // Kita set null dulu sementara waktu
                        
                        // Perbaikan: Pastikan total poin HANYA mengambil dari poin di periode tersebut. 
                        // Jika tidak ada poin di periode itu, maka 0.
                        'total_poin' => (int) $user->total_poin_periode,
                        
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