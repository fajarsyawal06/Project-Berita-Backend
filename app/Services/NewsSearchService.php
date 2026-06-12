<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\DB;

class NewsSearchService
{
    /**
     * Terapkan algoritma Full-Text Search (MySQL) pada query.
     */
    public function applySearch($query, $searchKeyword)
    {
        if (empty($searchKeyword)) {
            return $query;
        }

        // Pecah keyword menjadi beberapa kata, lalu buat mode boolean (+kata1 +kata2*)
        // untuk mendukung pencarian fuzzy dan mandatory
        $words = explode(' ', trim($searchKeyword));
        $booleanSearch = '';
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word)) {
                $booleanSearch .= '+' . $word . '* '; // Asterisk untuk pencocokan awalan (fuzzy)
            }
        }
        $booleanSearch = trim($booleanSearch);

        // Tambahkan skor relevansi ke dalam select
        // Menggunakan selectRaw agar binding parameter lebih aman dan tidak menimpa binding lainnya
        if (empty($query->getQuery()->columns)) {
            $query->select('news.*');
        }
        $query->selectRaw("MATCH(news.search_vector) AGAINST(? IN BOOLEAN MODE) AS relevance_score", [$booleanSearch]);

        // Filter data yang cocok saja (Kombinasi FULLTEXT dan LIKE fallback untuk judul)
        $query->where(function($q) use ($booleanSearch, $searchKeyword) {
            $q->whereRaw("MATCH(news.search_vector) AGAINST(? IN BOOLEAN MODE)", [$booleanSearch])
              ->orWhere('news.judul', 'LIKE', "%{$searchKeyword}%");
        });

        // Urutkan berdasarkan relevansi tertinggi
        $query->orderByDesc('relevance_score');

        return $query;
    }

    /**
     * Fungsi untuk menandai kata yang dicari dengan tag <mark> (Highlighting).
     */
    public function highlightResults($paginator, $searchKeyword)
    {
        if (empty($searchKeyword)) {
            return $paginator;
        }

        $words = explode(' ', trim($searchKeyword));
        $words = array_filter($words, fn($w) => !empty(trim($w)));

        if (empty($words)) {
            return $paginator;
        }

        // Buat pattern regex: (kata1|kata2) secara case-insensitive
        $pattern = '/(' . implode('|', array_map('preg_quote', $words)) . ')/i';

        $paginator->getCollection()->transform(function ($news) use ($pattern) {
            // Terapkan highlight ke atribut 5W1H dan Judul sesuai FR-TP-02 Skenario 1
            $fieldsToHighlight = [
                'judul', 'what_content', 'who_involved', 'when_occurred', 
                'where_location', 'why_happened', 'how_resolved'
            ];

            foreach ($fieldsToHighlight as $field) {
                if (!empty($news->$field)) {
                    // Bungkus hasil regex dengan tag <mark>
                    $news->$field = preg_replace($pattern, '<mark>$1</mark>', $news->$field);
                }
            }
            
            // Highlight juga ke relasi jika sudah di-load (opsional untuk Skenario 2)
            if ($news->relationLoaded('user') && $news->user) {
                $news->user->nama_lengkap = preg_replace($pattern, '<mark>$1</mark>', $news->user->nama_lengkap);
            }
            if ($news->relationLoaded('satuanKerja') && $news->satuanKerja) {
                $news->satuanKerja->nama_satuan_kerja = preg_replace($pattern, '<mark>$1</mark>', $news->satuanKerja->nama_satuan_kerja);
            }

            return $news;
        });

        return $paginator;
    }
}
