<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointHistory;
use Illuminate\Support\Facades\Auth;

class PointLedgerController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tahun' => 'nullable|integer|digits:4',
            'semester' => 'nullable|integer|in:1,2',
        ]);

        $user = Auth::user();

        $query = PointHistory::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc');

        if ($request->has('tahun')) {
            $tahun = $request->tahun;
            if ($request->has('semester')) {
                $semester = $request->semester;
                if ($semester == 1) {
                    $query->whereYear('created_at', $tahun)
                          ->whereMonth('created_at', '>=', 1)
                          ->whereMonth('created_at', '<=', 6);
                } elseif ($semester == 2) {
                    $query->whereYear('created_at', $tahun)
                          ->whereMonth('created_at', '>=', 7)
                          ->whereMonth('created_at', '<=', 12);
                }
            } else {
                $query->whereYear('created_at', $tahun);
            }
        }

        $ledgers = $query->get();

        $formattedLedgers = $ledgers->map(function ($ledger) {
            return [
                'tanggal' => $ledger->created_at->format('Y-m-d H:i:s'),
                'aktivitas' => $ledger->activity_type,
                'poin' => ($ledger->jumlah_poin > 0 ? '+' : '') . $ledger->jumlah_poin,
                'referensi_id' => $ledger->reference_id,
                'sumber_berita_laporan' => $ledger->reference_type,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat poin pengguna',
            'data' => $formattedLedgers,
        ]);
    }
}
