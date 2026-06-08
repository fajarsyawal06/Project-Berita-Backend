<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;

class ReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        ReportTemplate::updateOrCreate(
            ['type' => 'bulanan_satker'],
            [
                'name' => 'Laporan Bulanan Satuan Kerja',
                'is_active' => true,
                'layout_config' => ['orientation' => 'portrait', 'paper_size' => 'A4']
            ]
        );

        ReportTemplate::updateOrCreate(
            ['type' => 'kinerja_kontributor'],
            [
                'name' => 'Laporan Kinerja Kontributor',
                'is_active' => true,
                'layout_config' => ['orientation' => 'landscape', 'paper_size' => 'A4']
            ]
        );

        ReportTemplate::updateOrCreate(
            ['type' => 'analitik_trending'],
            [
                'name' => 'Laporan Analitik Trending',
                'is_active' => true,
                'layout_config' => ['orientation' => 'portrait', 'paper_size' => 'A4']
            ]
        );
    }
}
