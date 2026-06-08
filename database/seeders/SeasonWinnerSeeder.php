<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeasonWinner;

class SeasonWinnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Bersihkan data lama jika ada
        SeasonWinner::truncate();

        $tahun = 2026;
        $semester = 'Semester 1';

        // Dummy Juara 1
        SeasonWinner::create([
            'user_id' => 1, // Anggap user ID 1
            'tahun' => $tahun,
            'semester' => $semester,
            'peringkat' => 1,
            'total_poin' => 15200,
            'nama_lengkap_snapshot' => 'Ahmad Fajar Syawal',
            'satuan_kerja_snapshot' => 'Pusat Teknologi Informasi',
            'avatar_snapshot' => null,
        ]);

        // Dummy Juara 2
        SeasonWinner::create([
            'user_id' => 2, // Anggap user ID 2
            'tahun' => $tahun,
            'semester' => $semester,
            'peringkat' => 2,
            'total_poin' => 13500,
            'nama_lengkap_snapshot' => 'Budi Santoso',
            'satuan_kerja_snapshot' => 'Biro Komunikasi Publik',
            'avatar_snapshot' => null,
        ]);

        // Dummy Juara 3
        SeasonWinner::create([
            'user_id' => 3, // Anggap user ID 3
            'tahun' => $tahun,
            'semester' => $semester,
            'peringkat' => 3,
            'total_poin' => 11450,
            'nama_lengkap_snapshot' => 'Citra Kirana',
            'satuan_kerja_snapshot' => 'Pusat Data dan Statistik',
            'avatar_snapshot' => null,
        ]);
    }
}
