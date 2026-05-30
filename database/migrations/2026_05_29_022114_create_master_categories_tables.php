<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Data: Kategori Berita (Sesuai FR-MD-03)
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
            $table->text('deskripsi')->nullable();
            
            // Konfigurasi Visual UI/UX
            $table->string('warna_badge', 20)->default('#3B82F6'); // Default warna biru (Hex)
            $table->string('ikon', 50)->nullable(); // Nama class icon (misal: 'fa-fire' atau 'lucide-alert')
            $table->integer('urutan_tampilan')->default(0);
            
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 2. Master Data: Kategori Video & Panduan (Sesuai FR-PM-03)
        Schema::create('tutorial_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('ikon', 50)->nullable();
            $table->integer('urutan_tampilan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutorial_categories');
        Schema::dropIfExists('news_categories');
    }
};