<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom kode_berita di tabel news
        Schema::table('news', function (Blueprint $table) {
            $table->string('kode_berita', 50)->unique()->after('id')->nullable();
        });

        // Tambah kolom jabatan di tabel users (jika belum ada)
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable()->after('nama_lengkap');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('kode_berita');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jabatan');
        });
    }
};