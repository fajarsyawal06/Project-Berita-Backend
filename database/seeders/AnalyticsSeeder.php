<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Pastikan ada kategori untuk menghindari error
        if (NewsCategory::count() == 0) {
            NewsCategory::insert([
                ['kode_kategori' => 'KTG-A01', 'nama_kategori' => 'Politik', 'warna_badge' => '#EF4444', 'ikon' => 'ri-shield-line', 'urutan_tampilan' => 1, 'status_aktif' => true],
                ['kode_kategori' => 'KTG-A02', 'nama_kategori' => 'Ekonomi', 'warna_badge' => '#3B82F6', 'ikon' => 'ri-building-line', 'urutan_tampilan' => 2, 'status_aktif' => true],
                ['kode_kategori' => 'KTG-A03', 'nama_kategori' => 'Hiburan', 'warna_badge' => '#8B5CF6', 'ikon' => 'ri-group-line', 'urutan_tampilan' => 3, 'status_aktif' => true],
                ['kode_kategori' => 'KTG-A04', 'nama_kategori' => 'Olahraga', 'warna_badge' => '#F59E0B', 'ikon' => 'ri-alert-line', 'urutan_tampilan' => 4, 'status_aktif' => true],
            ]);
        }

        $categoryIds = NewsCategory::pluck('id')->toArray();
        if (empty($categoryIds)) {
            $categoryIds = [1, 2, 3, 4];
        }

        $newsData = [];
        $now = Carbon::now();

        // Buat 200 artikel tersebar dalam 6 bulan terakhir
        for ($i = 0; $i < 200; $i++) {
            $judul = $faker->realText(60);
            
            // Randomize date between now and 6 months ago
            $randomDays = rand(0, 180);
            $createdAt = $now->copy()->subDays($randomDays);

            $newsData[] = [
                'user_id'          => $faker->numberBetween(1, 5), // Asumsi user ada 5 utama + 15 leaderboard
                'category_id'      => $faker->randomElement($categoryIds),
                'satuan_kerja_id'  => $faker->numberBetween(1, 13), // 13 satker
                'kode_berita'      => 'ANL-' . date('Y') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-' . time() . rand(10, 999),
                'what_content'     => $faker->realText(300),
                'who_involved'     => $faker->name(),
                'when_occurred'    => $createdAt->format('Y-m-d H:i:s'),
                'where_location'   => $faker->city(),
                'why_happened'     => $faker->realText(100),
                'how_resolved'     => $faker->realText(80),
                'status'           => 'PUBLISHED',
                'views_count'      => rand(50, 15000), // Random views tinggi untuk grafik analitik
                'shares_count'     => rand(10, 500),
                'comments_count'   => rand(5, 200),
                'created_at'       => $createdAt->format('Y-m-d H:i:s'),
                'updated_at'       => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        // Insert massal
        foreach(array_chunk($newsData, 50) as $chunk) {
            News::insert($chunk);
        }
    }
}
