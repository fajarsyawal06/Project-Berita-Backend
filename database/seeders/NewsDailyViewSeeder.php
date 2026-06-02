<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsDailyViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newsIds = \App\Models\News::pluck('id')->toArray();

        if (empty($newsIds)) {
            $this->command->info('Tidak ada berita untuk di-seed.');
            return;
        }

        $records = [];
        // Buat data view palsu untuk 10 hari terakhir
        for ($i = 0; $i < 10; $i++) {
            $date = now()->subDays($i)->toDateString();
            
            // Pilih 5 berita acak per hari untuk di-view
            $randomNews = collect($newsIds)->random(min(5, count($newsIds)));
            
            foreach ($randomNews as $newsId) {
                $records[] = [
                    'news_id' => $newsId,
                    'view_date' => $date,
                    'views_count' => rand(10, 500), // Random views antara 10 - 500
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert using upsert or insert
        \App\Models\NewsDailyView::insert($records);
    }
}
