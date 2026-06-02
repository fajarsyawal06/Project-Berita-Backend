<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('news:populate-search')]
#[Description('Repopulate search_vector for all existing news')]
class PopulateSearchVector extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengisian search_vector untuk berita lama...');
        
        $newsList = \App\Models\News::all();
        $bar = $this->output->createProgressBar(count($newsList));

        foreach ($newsList as $news) {
            // Cukup save(), karena kita sudah menaruh logic di model event 'saving'
            // untuk menggenerate $news->search_vector
            $news->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai! Semua berita lama sekarang bisa dicari menggunakan Full-Text Search.');
    }
}
