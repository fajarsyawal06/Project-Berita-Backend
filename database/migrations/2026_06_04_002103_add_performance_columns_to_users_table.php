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
    Schema::table('users', function (Blueprint $table) {
        // Menambahkan kolom poin dan durasi online (dalam satuan menit agar akurat)
        $table->integer('poin_aktif')->default(0)->after('email');
        $table->integer('durasi_online_menit')->default(0)->after('poin_aktif');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['poin_aktif', 'durasi_online_menit']);
    });
}
};
