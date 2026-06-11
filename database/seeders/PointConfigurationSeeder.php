<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointConfiguration;

class PointConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'jenis_aktivitas' => 'Membuat Berita',
                'poin' => 10,
                'deskripsi' => 'Diberikan saat berita selesai diverifikasi dan diterbitkan.',
                'status_aktif' => true,
            ],
            [
                'jenis_aktivitas' => 'Verifikasi Berita',
                'poin' => 5,
                'deskripsi' => 'Diberikan saat Editor menyetujui berita kontributor.',
                'status_aktif' => true,
            ],
            [
                'jenis_aktivitas' => 'Share Berita',
                'poin' => 2,
                'deskripsi' => 'Diberikan saat pengguna membagikan berita.',
                'status_aktif' => true,
            ],
            [
                'jenis_aktivitas' => 'Menonton Video Panduan',
                'poin' => 3,
                'deskripsi' => 'Diberikan setelah selesai menonton video panduan.',
                'status_aktif' => true,
            ],
            [
                'jenis_aktivitas' => 'Mengisi Laporan',
                'poin' => 15,
                'deskripsi' => 'Diberikan saat pengguna mengisi dan mensubmit laporan.',
                'status_aktif' => true,
            ],
        ];

        foreach ($configs as $config) {
            PointConfiguration::updateOrCreate(
                ['jenis_aktivitas' => $config['jenis_aktivitas']],
                $config
            );
        }
    }
}
