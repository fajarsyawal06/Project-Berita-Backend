<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointHistory;
use App\Models\User;
use Carbon\Carbon;

class PointHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa user (maksimal 5) untuk dijadikan sampel
        // Jika tidak ada user, seeder akan berhenti agar tidak error
        $users = User::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('Tidak ada data user. Silakan buat user terlebih dahulu.');
            return;
        }

        $dummyData = [
            // --- SEMESTER 1 (2026) ---
            [
                'jumlah_poin' => 50,
                'deskripsi' => 'Publikasi Berita A',
                'created_at' => Carbon::create(2026, 2, 15, 10, 0, 0), // Februari 2026
            ],
            [
                'jumlah_poin' => 30,
                'deskripsi' => 'Berita Tembus 100 Views',
                'created_at' => Carbon::create(2026, 4, 10, 14, 30, 0), // April 2026
            ],
            [
                'jumlah_poin' => 20,
                'deskripsi' => 'Publikasi Berita B',
                'created_at' => Carbon::create(2026, 6, 25, 9, 15, 0), // Juni 2026
            ],
            [
                'jumlah_poin' => 40,
                'deskripsi' => 'Publikasi Berita C',
                'created_at' => Carbon::create(2026, 1, 5, 8, 0, 0), // Januari 2026
            ],
            [
                'jumlah_poin' => 15,
                'deskripsi' => 'Share Berita D',
                'created_at' => Carbon::create(2026, 5, 20, 16, 45, 0), // Mei 2026
            ],

            // --- SEMESTER 2 (2026) ---
            [
                'jumlah_poin' => 60,
                'deskripsi' => 'Publikasi Berita E (Semester 2)',
                'created_at' => Carbon::create(2026, 8, 12, 11, 0, 0), // Agustus 2026
            ],
            [
                'jumlah_poin' => 25,
                'deskripsi' => 'Publikasi Berita F (Semester 2)',
                'created_at' => Carbon::create(2026, 10, 5, 13, 0, 0), // Oktober 2026
            ],
            [
                'jumlah_poin' => 35,
                'deskripsi' => 'Berita G Tembus 500 Views',
                'created_at' => Carbon::create(2026, 12, 20, 15, 0, 0), // Desember 2026
            ],

            // --- TAHUN LAIN (2025) ---
            [
                'jumlah_poin' => 100,
                'deskripsi' => 'Penghargaan Tahunan 2025',
                'created_at' => Carbon::create(2025, 11, 10, 10, 0, 0), // November 2025
            ],
            [
                'jumlah_poin' => 45,
                'deskripsi' => 'Publikasi Berita H (2025)',
                'created_at' => Carbon::create(2025, 3, 5, 9, 0, 0), // Maret 2025
            ],
        ];

        // Masukkan data dummy secara acak ke user yang ada
        foreach ($dummyData as $data) {
            PointHistory::create([
                'user_id' => $users->random()->id,
                'jumlah_poin' => $data['jumlah_poin'],
                'deskripsi' => $data['deskripsi'],
                'created_at' => $data['created_at'],
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('10 Data Dummy Point History berhasil dibuat!');
    }
}
