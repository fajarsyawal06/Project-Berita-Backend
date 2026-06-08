<?php

namespace App\Services;

use App\Models\News;
use App\Models\SatuanKerja;
use App\Models\ReportTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportGeneratorService
{
    public function generate($templateType, $periode, $satuanKerjaId = null)
    {
        $template = ReportTemplate::where('type', $templateType)->firstOrFail();

        // Decode periode (e.g., '2026-05')
        $date = Carbon::createFromFormat('Y-m', $periode);
        $month = $date->month;
        $year = $date->year;
        $periodeLabel = $date->translatedFormat('F Y');

        $data = [];
        $viewName = '';

        if ($templateType === 'bulanan_satker') {
            $data = $this->getBulananSatkerData($satuanKerjaId, $month, $year);
            $data['periode'] = $periodeLabel;
            $data['satuan_kerja'] = SatuanKerja::find($satuanKerjaId)->nama_satuan_kerja ?? 'Semua Satuan Kerja';
            $viewName = 'reports.bulanan_satker';
        } elseif ($templateType === 'kinerja_kontributor') {
            $data = $this->getKinerjaKontributorData($satuanKerjaId, $month, $year);
            $data['periode'] = $periodeLabel;
            $data['satuan_kerja'] = $satuanKerjaId ? SatuanKerja::find($satuanKerjaId)->nama_satuan_kerja : 'Semua Satuan Kerja';
            $viewName = 'reports.kinerja_kontributor';
        } else {
            throw new \Exception("Template not supported yet");
        }

        $pdf = Pdf::loadView($viewName, $data);
        
        // Optional layout config
        if ($template->layout_config) {
            $paperSize = $template->layout_config['paper_size'] ?? 'a4';
            $orientation = $template->layout_config['orientation'] ?? 'portrait';
            $pdf->setPaper($paperSize, $orientation);
        }

        return $pdf;
    }

    private function getBulananSatkerData($satuanKerjaId, $month, $year)
    {
        $query = News::whereMonth('created_at', $month)
                     ->whereYear('created_at', $year)
                     ->where('status', 'PUBLISHED');

        if ($satuanKerjaId) {
            $query->where('satuan_kerja_id', $satuanKerjaId);
        }

        $totalNews = $query->count();
        $totalViewers = $query->sum('views_count');

        // Top Kontributor (di dalam Satker)
        $topContributor = News::select('user_id', DB::raw('COUNT(id) as total_news'))
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status', 'PUBLISHED');
            
        if ($satuanKerjaId) {
            $topContributor->where('satuan_kerja_id', $satuanKerjaId);
        }
        
        $topContributor = $topContributor->groupBy('user_id')
            ->orderByDesc('total_news')
            ->with('user')
            ->first();

        // Hitung peringkat nasional (berdasarkan jumlah berita bulan ini)
        $nationalRanking = 0;
        if ($satuanKerjaId) {
            $rankings = News::select('satuan_kerja_id', DB::raw('COUNT(id) as total_news'))
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('status', 'PUBLISHED')
                ->groupBy('satuan_kerja_id')
                ->orderByDesc('total_news')
                ->get();
            
            foreach ($rankings as $index => $rank) {
                if ($rank->satuan_kerja_id == $satuanKerjaId) {
                    $nationalRanking = $index + 1;
                    break;
                }
            }
        }

        return [
            'total_news' => $totalNews,
            'total_viewers' => $totalViewers,
            'top_contributor_name' => $topContributor && $topContributor->user ? $topContributor->user->nama_lengkap : '-',
            'top_contributor_news' => $topContributor ? $topContributor->total_news : 0,
            'national_ranking' => $nationalRanking > 0 ? $nationalRanking : '-',
        ];
    }

    private function getKinerjaKontributorData($satuanKerjaId, $month, $year)
    {
        // Simple mock for now
        $query = News::with('user')
            ->select('user_id', DB::raw('COUNT(id) as total_news'), DB::raw('SUM(views_count) as total_views'))
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status', 'PUBLISHED')
            ->groupBy('user_id')
            ->orderByDesc('total_news');

        if ($satuanKerjaId) {
            $query->where('satuan_kerja_id', $satuanKerjaId);
        }

        return [
            'contributors' => $query->get()
        ];
    }
}
