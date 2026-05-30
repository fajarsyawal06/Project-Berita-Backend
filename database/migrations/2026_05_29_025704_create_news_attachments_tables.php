<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_attachments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel news utama
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            
            // Klasifikasi Jenis Media sesuai multiformat PRD
            $table->enum('file_type', [
                'image', 
                'video', 
                'document', 
                'voice_note', 
                'external_link'
            ]);

            // Path penyimpanan di storage lokal/cloud atau URL eksternal
            $table->string('file_path'); 
            
            // Metadata file untuk frontend
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable(); // cth: 'application/pdf', 'audio/mp3'
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_attachments');
    }
};