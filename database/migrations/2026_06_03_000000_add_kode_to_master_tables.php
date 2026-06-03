<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'kode_jabatan')) {
                $table->string('kode_jabatan', 20)->nullable()->unique()->after('id');
            }
        });

        Schema::table('news_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('news_categories', 'kode_kategori')) {
                $table->string('kode_kategori', 20)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'kode_jabatan')) {
                $table->dropColumn('kode_jabatan');
            }
        });

        Schema::table('news_categories', function (Blueprint $table) {
            if (Schema::hasColumn('news_categories', 'kode_kategori')) {
                $table->dropColumn('kode_kategori');
            }
        });
    }
};
