<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeasonWinner;
use Illuminate\Http\Request;

class SeasonWinnerController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->input('tahun');
        $semester = $request->input('semester');

        $query = SeasonWinner::query();

        if ($tahun && $semester) {
            $query->where('tahun', $tahun)->where('semester', $semester);
        } else {
            // Jika tidak ada parameter, ambil pemenang dari musim/periode terbaru yang ada di database
            $latestWinner = SeasonWinner::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->first();
            
            if ($latestWinner) {
                $query->where('tahun', $latestWinner->tahun)
                      ->where('semester', $latestWinner->semester);
            }
        }

        $winners = $query->orderBy('peringkat', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Pemenang Semester berhasil dimuat',
            'data' => $winners
        ], 200);
    }
}
