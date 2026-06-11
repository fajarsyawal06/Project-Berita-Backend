<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->integer('level_hierarki')->nullable()->after('nama_jabatan');
            $table->foreignId('satuan_kerja_id')->nullable()->constrained('satuan_kerjas')->nullOnDelete()->after('level_hierarki');
            $table->text('deskripsi')->nullable()->after('satuan_kerja_id');
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['satuan_kerja_id']);
            $table->dropColumn(['level_hierarki', 'satuan_kerja_id', 'deskripsi']);
        });
    }
};
