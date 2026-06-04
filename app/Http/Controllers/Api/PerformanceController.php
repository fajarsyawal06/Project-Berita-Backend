<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerformanceController extends Controller
{
   public function index(Request $request)
    {
        // 1. PERBAIKAN: Gunakan 'satuanKerja' sesuai nama fungsi di Model User.php
        $user = Auth::user()->load(['satuanKerja', 'badges']); 

        // 2. Hitung total berita 
        $totalBerita = $user->news()->count();

        // 3. Hitung total penonton 
        // Pastikan kolom 'views' benar-benar ada di tabel news kamu 
        $totalViews = $user->news()->sum('views_count') ?? 0;

        // 4. Konversi durasi online dari menit ke jam (dibulatkan 1 angka di belakang koma)
        $durasiOnlineJam = round($user->durasi_online_menit / 60, 1); 

        // 5. Hitung Tren 7 Hari Terakhir
        $trend7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subDays($i)->format('Y-m-d');
            $jumlah = $user->news()->whereDate('created_at', $tanggal)->count();
            
            $trend7Hari[] = [
                'tanggal' => $tanggal,
                'jumlah_berita' => $jumlah
            ];
        }

        // 6. Susun Respons JSON agar rapi untuk Frontend
        return response()->json([
            'status' => 'success',
            'data' => [
                'profil' => [
                    // PERBAIKAN: Gunakan nama_lengkap sesuai fillable di User.php
                    'nama_lengkap' => $user->nama_lengkap, 
                    // PERBAIKAN: Panggil satuanKerja
                    'satuan_kerja' => $user->satuanKerja ? $user->satuanKerja->nama : 'Belum diatur',
                    'avatar' => $user->avatar ?? null, 
                ],
                'statistik' => [
                    'total_berita' => $totalBerita,
                    'total_viewers' => (int) $totalViews,
                    'akumulasi_poin' => $user->poin_aktif,
                    'durasi_online_jam' => $durasiOnlineJam,
                ],
                'trend_7_hari' => $trend7Hari,
                'badges' => $user->badges->map(function ($badge) {
                    return [
                        'nama_badge' => $badge->nama_badge,
                        'ikon' => $badge->ikon,
                        'tanggal_peroleh' => $badge->pivot->tanggal_peroleh,
                    ];
                })
            ]
        ], 200);
    }
}