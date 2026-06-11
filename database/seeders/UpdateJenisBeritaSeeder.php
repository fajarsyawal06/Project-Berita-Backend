<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateJenisBeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisBerita = ['TEKS', 'VIDEO', 'FOTO'];
        $jenisPublikasi = ['INTERNAL', 'UMUM'];

        $news = DB::table('news')->get();

        foreach ($news as $item) {
            DB::table('news')
                ->where('id', $item->id)
                ->update([
                    'jenis_berita' => $jenisBerita[array_rand($jenisBerita)],
                    'jenis_publikasi' => $jenisPublikasi[array_rand($jenisPublikasi)]
                ]);
        }

        $this->command->info('Berhasil mengacak jenis berita dan publikasi untuk ' . $news->count() . ' berita lama!');
    }
}
