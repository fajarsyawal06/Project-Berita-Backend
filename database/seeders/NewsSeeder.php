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
        // 1. Buat Kategori Berita yang Realistis
        $categories = [
            ['kode_kategori' => 'KTG-001', 'nama_kategori' => 'Infrastruktur & Pembangunan', 'warna_badge' => '#3B82F6', 'ikon' => 'ri-building-line', 'urutan_tampilan' => 1, 'status_aktif' => true],
            ['kode_kategori' => 'KTG-002', 'nama_kategori' => 'Sosial & Budaya', 'warna_badge' => '#8B5CF6', 'ikon' => 'ri-group-line', 'urutan_tampilan' => 2, 'status_aktif' => true],
            ['kode_kategori' => 'KTG-003', 'nama_kategori' => 'Keamanan & Ketertiban', 'warna_badge' => '#EF4444', 'ikon' => 'ri-shield-line', 'urutan_tampilan' => 3, 'status_aktif' => true],
            ['kode_kategori' => 'KTG-004', 'nama_kategori' => 'Bencana Alam', 'warna_badge' => '#F59E0B', 'ikon' => 'ri-alert-line', 'urutan_tampilan' => 4, 'status_aktif' => true],
            ['kode_kategori' => 'KTG-005', 'nama_kategori' => 'Pendidikan & Teknologi', 'warna_badge' => '#10B981', 'ikon' => 'ri-macbook-line', 'urutan_tampilan' => 5, 'status_aktif' => true],
        ];

        foreach ($categories as $cat) {
            NewsCategory::updateOrCreate(['nama_kategori' => $cat['nama_kategori']], $cat);
        }

        // 2. Buat Kategori Video Panduan
        TutorialCategory::updateOrCreate(['nama_kategori' => 'Panduan Aplikasi'], ['ikon' => 'monitor']);
        TutorialCategory::updateOrCreate(['nama_kategori' => 'SOP Jurnalistik'], ['ikon' => 'book-open']);

        // 3. Buat Data Berita Realistic

        // Berita 1: Status PUBLISHED
        $judul1 = 'Peresmian Pusat Pelatihan Teknologi Informasi di Jawa Barat';
        $news1 = News::create([
            'user_id'          => 1, // Andi Saputra (Kontributor)
            'category_id'      => 5, // Pendidikan & Teknologi
            'satuan_kerja_id'  => 2, // Kanwil Jabar
            'judul'            => $judul1,
            'slug'             => Str::slug($judul1) . '-' . time(),
            'what_content'     => 'Telah dilaksanakan acara peresmian Pusat Pelatihan Teknologi Informasi tingkat provinsi yang ditujukan untuk meningkatkan kompetensi digital aparatur sipil negara dan masyarakat umum.',
            'who_involved'     => 'Gubernur Jawa Barat, Kepala Kanwil Jabar, dan perwakilan dari Kementerian Komunikasi.',
            'when_occurred'    => 'Rabu, 18 Mei 2026 pukul 09:00 WIB.',
            'where_location'   => 'Gedung Sate, Bandung, Jawa Barat.',
            'why_happened'     => 'Program ini merupakan bagian dari inisiatif nasional untuk mempercepat transformasi digital di daerah.',
            'how_resolved'     => 'Acara berlangsung lancar dan ditutup dengan pemotongan pita serta penandatanganan prasasti.',
            'latitude'         => -6.902481, 
            'longitude'        => 107.618810,
            'location_address' => 'Jl. Diponegoro No.22, Citarum, Kec. Bandung Wetan, Kota Bandung',
            'status'           => 'PUBLISHED',
            'views_count'      => 345,
            'thumbnail'        => 'https://picsum.photos/seed/news1/800/600',
            'jenis_publikasi'  => 'UMUM',
            'jenis_berita'     => 'TEKS',
        ]);

        NewsAttachment::create([
            'news_id'           => $news1->id,
            'file_type'         => 'image',
            'file_path'         => 'news_attachments/peresmian_jabar.jpg',
            'original_filename' => 'foto_peresmian_gedung.jpg',
            'mime_type'         => 'image/jpeg',
            'file_size_bytes'   => 2048000,
        ]);

        // Berita 2: Status SENT_WAITING_VERIFICATION
        $judul2 = 'Banjir Bandang Melanda Sebagian Wilayah Kabupaten Bandung';
        $news2 = News::create([
            'user_id'          => 1,
            'category_id'      => 4, // Bencana Alam
            'satuan_kerja_id'  => 2, // Kanwil Jabar
            'judul'            => $judul2,
            'slug'             => Str::slug($judul2) . '-' . time(),
            'what_content'     => 'Hujan lebat yang mengguyur sejak semalam mengakibatkan meluapnya sungai Citarum dan merendam puluhan rumah warga di Kabupaten Bandung.',
            'who_involved'     => 'Badan Penanggulangan Bencana Daerah (BPBD) Jawa Barat dan relawan lokal.',
            'when_occurred'    => 'Selasa, 17 Mei 2026 pukul 03:00 WIB.',
            'where_location'   => 'Kecamatan Dayeuhkolot dan Baleendah, Kabupaten Bandung.',
            'why_happened'     => 'Curah hujan yang ekstrem dan sistem drainase yang tidak mampu menampung debit air kiriman.',
            'how_resolved'     => 'Tim gabungan BPBD sedang melakukan evakuasi warga menggunakan perahu karet dan mendirikan posko pengungsian di dataran yang lebih tinggi.',
            'latitude'         => -6.993444,
            'longitude'        => 107.622436,
            'location_address' => 'Kecamatan Dayeuhkolot, Kabupaten Bandung',
            'status'           => 'SENT_WAITING_VERIFICATION',
            'thumbnail'        => 'https://picsum.photos/seed/news2/800/600',
            'jenis_publikasi'  => 'UMUM',
            'jenis_berita'     => 'TEKS',
        ]);

        // Berita 3: Status DRAFT
        $judul3 = 'Laporan Progres Pembangunan Jalan Tol Lintas Selatan';
        $news3 = News::create([
            'user_id'          => 1,
            'category_id'      => 1, // Infrastruktur & Pembangunan
            'satuan_kerja_id'  => 3, // Kanwil Jatim
            'judul'            => $judul3,
            'slug'             => Str::slug($judul3) . '-' . time(),
            'what_content'     => 'Pembangunan seksi ketiga jalan tol lintas selatan Jawa Timur dilaporkan mengalami keterlambatan.',
            'who_involved'     => 'Kementerian PUPR dan Kontraktor pelaksana BUMN Karya.',
            'when_occurred'    => null,
            'where_location'   => null,
            'why_happened'     => null,
            'how_resolved'     => null,
            'status'           => 'DRAFT',
            'thumbnail'        => 'https://picsum.photos/seed/news3/800/600',
            'jenis_publikasi'  => 'INTERNAL',
            'jenis_berita'     => 'TEKS',
        ]);

        NewsAttachment::create([
            'news_id'           => $news3->id,
            'file_type'         => 'document',
            'file_path'         => 'news_attachments/laporan_tol_jatim.pdf', 
            'original_filename' => 'laporan_mingguan_tol.pdf',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 1024000,
        ]);

        // Generate lebih banyak data dummy dengan Faker
        $faker = \Faker\Factory::create('id_ID');

        // Buat 15 Berita PUBLISHED untuk tes widget trending
        for ($i = 0; $i < 15; $i++) {
            $judul = $faker->realText(50);
            News::create([
                'user_id'          => $faker->numberBetween(1, 4),
                'category_id'      => $faker->numberBetween(1, 5),
                'satuan_kerja_id'  => $faker->numberBetween(1, 13),
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-pub-' . time() . $i,
                'what_content'     => $faker->realText(300),
                'who_involved'     => 'Pihak ' . $faker->company() . ' dan warga setempat.',
                'when_occurred'    => $faker->dateTimeThisMonth()->format('l, d F Y H:i'),
                'where_location'   => $faker->city() . ', ' . $faker->state(),
                'why_happened'     => $faker->realText(100),
                'how_resolved'     => 'Masih dalam proses pemantauan oleh tim terkait.',
                'latitude'         => $faker->latitude(-8, 5),
                'longitude'        => $faker->longitude(95, 140),
                'location_address' => $faker->address(),
                'status'           => 'PUBLISHED',
                'views_count'      => $faker->numberBetween(50, 2000),
                'shares_count'     => $faker->numberBetween(5, 300),
                'comments_count'   => $faker->numberBetween(0, 150),
                'created_at'       => $faker->dateTimeBetween('-1 month', 'now'),
                'thumbnail'        => 'https://picsum.photos/seed/pub' . $i . '/800/600',
                'jenis_publikasi'  => $faker->randomElement(['INTERNAL', 'UMUM']),
                'jenis_berita'     => $faker->randomElement(['TEKS', 'VIDEO', 'FOTO']),
            ]);
        }

        // Buat 8 Berita SENT_WAITING_VERIFICATION untuk tes antrean
        for ($i = 0; $i < 8; $i++) {
            $judul = $faker->realText(60);
            News::create([
                'user_id'          => $faker->numberBetween(1, 3),
                'category_id'      => $faker->numberBetween(1, 5),
                'satuan_kerja_id'  => $faker->numberBetween(1, 13),
                'judul'            => $judul,
                'slug'             => Str::slug($judul) . '-wait-' . time() . $i,
                'what_content'     => $faker->realText(250),
                'who_involved'     => $faker->name(),
                'when_occurred'    => $faker->dateTimeThisMonth()->format('l, d F Y H:i'),
                'where_location'   => $faker->city(),
                'why_happened'     => $faker->realText(100),
                'how_resolved'     => $faker->realText(80),
                'status'           => 'SENT_WAITING_VERIFICATION',
                'thumbnail'        => 'https://picsum.photos/seed/wait' . $i . '/800/600',
                'jenis_publikasi'  => $faker->randomElement(['INTERNAL', 'UMUM']),
                'jenis_berita'     => $faker->randomElement(['TEKS', 'VIDEO', 'FOTO']),
            ]);
        }
    }
}