<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nip_pegawai', 50)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('email', 100)->unique();
            $table->string('password'); 
            $table->string('avatar')->nullable();
            
            // Relasi (Pastikan tabel ini dieksekusi setelah tabel master di atas)
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('satuan_kerja_id')->nullable()->constrained('satuan_kerjas')->nullOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();

            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Biarkan tabel password_reset_tokens dan sessions bawaan Laravel tetap ada di bawah sini jika Anda butuh
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};