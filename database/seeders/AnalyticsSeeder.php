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
                ['kode_kategori' => 'KTG-A01', 'nama_kategori' => 'Politik'],
                ['kode_kategori' => 'KTG-A02', 'nama_kategori' => 'Ekonomi'],
                ['kode_kategori' => 'KTG-A03', 'nama_kategori' => 'Hiburan'],
                ['kode_kategori' => 'KTG-A04', 'nama_kategori' => 'Olahraga'],
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
            $judul = $faker->sentence(6);
            
            // Randomize date between now and 6 months ago
            $randomDays = rand(0, 180);
            $createdAt = $now->copy()->subDays($randomDays);

            $newsData[] = [
                'user_id'          => 1, // Asumsikan user dengan ID 1 ada
                'category_id'      => $faker->randomElement($categoryIds),
                'satuan_kerja_id'  => 1, // Asumsikan satker ID 1 ada
                'kode_berita'      => 'ANL-' . date('Y') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-' . time() . rand(10, 999),
                'what_content'     => $faker->paragraph(),
                'who_involved'     => $faker->name(),
                'when_occurred'    => $createdAt->format('Y-m-d H:i:s'),
                'where_location'   => $faker->city(),
                'why_happened'     => $faker->sentence(),
                'how_resolved'     => $faker->sentence(),
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
