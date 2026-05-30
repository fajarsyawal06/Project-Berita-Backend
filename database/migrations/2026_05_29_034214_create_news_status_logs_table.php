<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_status_logs', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke berita yang diubah
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            
            // Relasi ke aktor (Siapa yang menekan tombol setuju/tolak/kirim)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Jejak perubahan status
            $table->string('old_status', 50)->nullable(); // Nullable untuk saat berita pertama kali dibuat (Draft)
            $table->string('new_status', 50);
            
            // Alasan penolakan (Wajib diisi di sisi backend JIKA status = REJECTED)
            $table->text('reason')->nullable();

            $table->timestamps(); // Menyimpan kapan aksi ini dilakukan (Audit Trail)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_status_logs');
    }
};