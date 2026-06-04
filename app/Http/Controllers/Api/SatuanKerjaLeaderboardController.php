<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanKerja;
use App\Models\User;

class SatuanKerjaLeaderboardController extends Controller
{
    /**
     * Get the leaderboard for Satuan Kerja (National or Local scope).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cakupan = $request->input('cakupan', 'nasional'); // 'nasional' or 'lokal'
        $wilayah = $request->input('wilayah');
        $perPage = $request->input('per_page', 15); // Default 15 data per halaman

        $query = SatuanKerja::query()
            ->withCount('users as total_anggota')
            ->withSum('users as total_poin_kumulatif', 'poin_aktif') // Ini menambahkan atribut total_poin_kumulatif
            ->withCount(['news as jumlah_berita' => function ($query) {
                $query->where('status', 'PUBLISHED'); // Hanya hitung berita yang published
            }]);

        if ($cakupan === 'lokal') {
            if (empty($wilayah)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter wilayah wajib diisi jika cakupan adalah lokal.',
                ], 400);
            }
            $query->where('provinsi_wilayah', $wilayah);
        }

        // Urutkan berdasarkan poin kumulatif terbanyak menggunakan subquery agar lebih aman
        $query->orderByDesc(
            User::selectRaw('COALESCE(SUM(poin_aktif), 0)')
                ->whereColumn('satuan_kerja_id', 'satuan_kerjas.id')
        );

        $paginated = $query->paginate($perPage);

        // Tambahkan atribut peringkat (ranking) berdasarkan urutan item di halaman tersebut
        $startRank = ($paginated->currentPage() - 1) * $paginated->perPage() + 1;

        $formattedData = $paginated->getCollection()->map(function ($item, $index) use ($startRank) {
            return [
                'peringkat' => $startRank + $index,
                'nama_satuan_kerja' => $item->nama_satuan_kerja,
                'total_anggota' => $item->total_anggota ?? 0,
                'total_poin_kumulatif' => (int) ($item->total_poin_kumulatif ?? 0),
                'jumlah_berita' => $item->jumlah_berita ?? 0,
            ];
        });

        // Setel kembali koleksi yang sudah diformat ke dalam paginator
        $paginated->setCollection($formattedData);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil peringkat akumulatif antar Satuan Kerja',
            'data' => $paginated,
        ]);
    }
}
