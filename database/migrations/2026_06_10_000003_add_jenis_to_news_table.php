<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->enum('jenis_berita', ['TEKS', 'VIDEO', 'FOTO'])->default('TEKS')->after('category_id');
            $table->enum('jenis_publikasi', ['INTERNAL', 'UMUM'])->default('UMUM')->after('jenis_berita');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['jenis_berita', 'jenis_publikasi']);
        });
    }
};
