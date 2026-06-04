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
    // Tabel Master Badge
    Schema::create('badges', function (Blueprint $table) {
        $table->id();
        $table->string('nama_badge');
        $table->string('ikon'); // Menyimpan nama file ikon atau class icon
        $table->string('deskripsi')->nullable();
        $table->timestamps();
    });

    // Tabel Pivot Hubungan Banyak-ke-Banyak (Many-to-Many) antara User dan Badge
    Schema::create('user_badge', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
        $table->timestamp('tanggal_peroleh')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('user_badge');
    Schema::dropIfExists('badges');
}
};
