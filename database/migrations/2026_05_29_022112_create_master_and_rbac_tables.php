<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Satuan Kerja
        Schema::create('satuan_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satuan_kerja');
            $table->timestamps();
        });

        // 2. Master Jabatan
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->timestamps();
        });

        // 3. Master Role
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('kode_role', 10)->unique();
            $table->string('nama_role', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('satuan_kerjas');
    }
};
