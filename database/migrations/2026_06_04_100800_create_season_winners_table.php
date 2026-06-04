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
        Schema::create('season_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('tahun');
            $table->string('semester'); // "Semester 1" atau "Semester 2"
            $table->integer('peringkat'); // 1, 2, atau 3
            $table->integer('total_poin');
            $table->string('nama_lengkap_snapshot');
            $table->string('satuan_kerja_snapshot')->nullable();
            $table->string('avatar_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_winners');
    }
};
