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

    public function showUserPoints(Request $request, $userId)
    {
        $ledgers = PointHistory::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $formattedLedgers = $ledgers->map(function ($ledger) {
            return [
                'id' => $ledger->id,
                'tanggal' => $ledger->created_at->format('Y-m-d H:i:s'),
                'aktivitas' => $ledger->activity_type,
                'poin' => ($ledger->jumlah_poin > 0 ? '+' : '') . $ledger->jumlah_poin,
                'referensi_id' => $ledger->reference_id,
                'sumber_berita_laporan' => $ledger->reference_type,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat poin',
            'data' => $formattedLedgers,
        ]);
    }

    public function reversal(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reference_id' => 'required|exists:point_histories,id',
            'reason' => 'required|string',
            'new_points' => 'nullable|integer'
        ]);

        $original = PointHistory::findOrFail($request->reference_id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $original) {
            // Reversal entry (negative of original)
            PointHistory::create([
                'user_id' => $request->user_id,
                'jumlah_poin' => -$original->jumlah_poin,
                'activity_type' => 'Reversal: ' . $request->reason,
                'reference_id' => $original->id,
                'reference_type' => 'REVERSAL'
            ]);

            // New correct entry if specified
            if ($request->has('new_points')) {
                PointHistory::create([
                    'user_id' => $request->user_id,
                    'jumlah_poin' => $request->new_points,
                    'activity_type' => 'Koreksi: ' . $request->reason,
                    'reference_id' => $original->id,
                    'reference_type' => 'CORRECTION'
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Koreksi poin berhasil dilakukan.'
        ]);
    }
}
