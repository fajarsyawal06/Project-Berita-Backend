<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_news_complete AS
            SELECT 
                n.id, 
                n.judul AS judul_berita, 
                n.status, 
                n.views_count AS jumlah_viewers, 
                n.created_at AS tanggal_dibuat, 
                n.updated_at AS tanggal_publish,
                u.nama_lengkap AS nama_pembuat, 
                sk.nama_satuan_kerja AS satuan_kerja,
                c.nama_kategori AS kategori_berita
            FROM news n
            LEFT JOIN users u ON n.user_id = u.id
            LEFT JOIN satuan_kerjas sk ON n.satuan_kerja_id = sk.id
            LEFT JOIN news_categories c ON n.category_id = c.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_news_complete");
    }
};
