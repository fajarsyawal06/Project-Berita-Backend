<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Master Data
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('news_categories');
            $table->foreignId('satuan_kerja_id')->constrained('satuan_kerjas');
            
            $table->string('judul', 255);
            $table->string('slug', 255)->unique();
            
            // Elemen Jurnalisme 5W+1H (Aman dari Reserved Keywords)
            $table->text('what_content')->nullable(); // nullable untuk fitur "Save as Draft"
            $table->text('who_involved')->nullable();
            $table->text('when_occurred')->nullable();
            $table->text('where_location')->nullable();
            $table->text('why_happened')->nullable();
            $table->text('how_resolved')->nullable();

            // Geolocation (Titik Peta)
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address')->nullable(); 

            // State Machine / Status sesuai FR-BR-02
            $table->enum('status', [
                'DRAFT', 
                'SENT_WAITING_VERIFICATION', // Disesuaikan dengan PRD
                'APPROVED', 
                'PUBLISHED', 
                'REJECTED'
            ])->default('DRAFT');

            // Tracking engagement
            $table->unsignedBigInteger('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes(); // Wajib agar audit trail tidak rusak jika berita dihapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};