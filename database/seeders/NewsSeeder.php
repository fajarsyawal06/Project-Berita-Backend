<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NewsCategory;
use App\Models\TutorialCategory;
use App\Models\News;
use App\Models\NewsAttachment;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori Berita
        $categories = [
            ['nama_kategori' => 'Keamanan & Ketertiban', 'warna_badge' => '#EF4444', 'ikon' => 'shield'],
            ['nama_kategori' => 'Bencana Alam', 'warna_badge' => '#F59E0B', 'ikon' => 'alert-triangle'],
            ['nama_kategori' => 'Infrastruktur', 'warna_badge' => '#3B82F6', 'ikon' => 'building'],
            ['nama_kategori' => 'Layanan Publik', 'warna_badge' => '#10B981', 'ikon' => 'users'],
        ];

        foreach ($categories as $cat) {
            NewsCategory::updateOrCreate(['nama_kategori' => $cat['nama_kategori']], $cat);
        }

        // 2. Buat Kategori Video Panduan
        TutorialCategory::updateOrCreate(['nama_kategori' => 'Panduan Aplikasi'], ['ikon' => 'monitor']);
        TutorialCategory::updateOrCreate(['nama_kategori' => 'SOP Jurnalistik'], ['ikon' => 'book-open']);

        // 3. Buat Data Berita Dummy

        // Berita 1: Status PUBLISHED
        $judul1 = 'Aksi Demonstrasi Damai Mahasiswa di Depan Balaikota';
        $news1 = News::create([
            'user_id'          => 1,
            'category_id'      => 1, // Keamanan
            'satuan_kerja_id'  => 1,
            'judul'            => $judul1,
            'slug'             => Str::slug($judul1) . '-' . time(),
            'what_content'     => 'Telah terjadi aksi demonstrasi damai di depan gedung balaikota.',
            'who_involved'     => 'Aliansi Mahasiswa dan aparat kepolisian setempat.',
            'when_occurred'    => 'Senin, 15 Mei pukul 10:00 WITA.',
            'where_location'   => 'Jalan Jenderal Sudirman, depan Gedung Pemerintahan.',
            'why_happened'     => 'Menuntut transparansi anggaran daerah tahun berjalan.',
            'how_resolved'     => 'Massa membubarkan diri dengan tertib pada pukul 14:00 WITA setelah ditemui oleh perwakilan dewan.',
            'latitude'         => -5.147665, 
            'longitude'        => 119.412613,
            'location_address' => 'Jl. Jend. Sudirman, Pusat Kota',
            'status'           => 'PUBLISHED',
            'views_count'      => 145,
        ]);

        NewsAttachment::create([
            'news_id'           => $news1->id,
            'file_type'         => 'image',
            'file_path'         => 'news_attachments/dummy_demo.jpg',
            'original_filename' => 'foto_demo_lapangan.jpg',
            'mime_type'         => 'image/jpeg',
            'file_size_bytes'   => 1024000,
        ]);

        // Berita 2: Status SENT_WAITING_VERIFICATION
        $judul2 = 'Pohon Tumbang Menutup Akses Jalan Lintas Provinsi KM 12';
        $news2 = News::create([
            'user_id'          => 1,
            'category_id'      => 2, // Bencana Alam
            'satuan_kerja_id'  => 1,
            'judul'            => $judul2,
            'slug'             => Str::slug($judul2) . '-' . time(),
            'what_content'     => 'Pohon tumbang menutup akses jalan raya provinsi.',
            'who_involved'     => 'Tim BPBD dan warga sekitar.',
            'when_occurred'    => 'Selasa, 16 Mei pukul 15:30 WITA.',
            'where_location'   => 'Jalan Lintas Provinsi KM 12.',
            'why_happened'     => 'Angin kencang disertai hujan lebat mengguyur wilayah tersebut selama 2 jam.',
            'how_resolved'     => 'Saat ini tim masih melakukan proses evakuasi dan pemotongan batang pohon. Jalan dialihkan sementara.',
            'latitude'         => -5.132214,
            'longitude'        => 119.421516,
            'location_address' => 'KM 12 Lintas Provinsi',
            'status'           => 'SENT_WAITING_VERIFICATION',
        ]);

        // Berita 3: Status DRAFT
        $judul3 = 'Laporan Keterlambatan Proyek Perbaikan Jembatan Desa';
        $news3 = News::create([
            'user_id'          => 1,
            'category_id'      => 3, // Infrastruktur
            'satuan_kerja_id'  => 1,
            'judul'            => $judul3,
            'slug'             => Str::slug($judul3) . '-' . time(),
            'what_content'     => 'Proyek perbaikan jembatan penghubung desa terhambat.',
            'who_involved'     => 'Kontraktor pelaksana CV Bangun Bersama.',
            'when_occurred'    => null,
            'where_location'   => null,
            'why_happened'     => null,
            'how_resolved'     => null,
            'status'           => 'DRAFT',
        ]);

        NewsAttachment::create([
            'news_id'           => $news3->id,
            'file_type'         => 'document',
            'file_path'         => 'news_attachments/laporan_proyek.pdf', 
            'original_filename' => 'laporan_mingguan_proyek.pdf',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 512000,
        ]);

        // Generate lebih banyak data dummy agar aplikasi terlihat padat (FR-BR-07)
        $faker = \Faker\Factory::create('id_ID');

        // Buat 15 Berita PUBLISHED untuk tes widget trending (beberapa di 24 jam terakhir)
        for ($i = 0; $i < 15; $i++) {
            $judul = $faker->sentence(6);
            News::create([
                'user_id'          => 1,
                'category_id'      => $faker->numberBetween(1, 4),
                'satuan_kerja_id'  => 1,
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-pub-' . time() . $i,
                'what_content'     => $faker->paragraph(),
                'who_involved'     => $faker->name() . ' dan warga.',
                'when_occurred'    => $faker->dateTimeThisMonth()->format('l, d F Y H:i'),
                'where_location'   => $faker->address(),
                'why_happened'     => $faker->sentence(10),
                'how_resolved'     => 'Sedang ditangani oleh pihak berwenang.',
                'latitude'         => $faker->latitude(-6, -5),
                'longitude'        => $faker->longitude(119, 120),
                'location_address' => $faker->streetAddress(),
                'status'           => 'PUBLISHED',
                'views_count'      => $faker->numberBetween(10, 500),
                'shares_count'     => $faker->numberBetween(0, 100),
                'comments_count'   => $faker->numberBetween(0, 50),
                'created_at'       => $faker->dateTimeBetween('-2 days', 'now'),
            ]);
        }

        // Buat 8 Berita SENT_WAITING_VERIFICATION untuk tes antrean
        for ($i = 0; $i < 8; $i++) {
            $judul = $faker->sentence(6);
            News::create([
                'user_id'          => 1,
                'category_id'      => $faker->numberBetween(1, 4),
                'satuan_kerja_id'  => 1,
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-wait-' . time() . $i,
                'what_content'     => $faker->paragraph(),
                'who_involved'     => $faker->name(),
                'when_occurred'    => $faker->dateTimeThisMonth()->format('l, d F Y H:i'),
                'where_location'   => $faker->address(),
                'why_happened'     => $faker->sentence(),
                'how_resolved'     => $faker->sentence(),
                'status'           => 'SENT_WAITING_VERIFICATION',
            ]);
        }
    }
}