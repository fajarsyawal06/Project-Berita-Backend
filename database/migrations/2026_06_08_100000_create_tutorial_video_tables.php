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
        Schema::create('tutorial_videos', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('url_video');
            $table->string('thumbnail')->nullable();
            $table->foreignId('tutorial_category_id')->nullable()->constrained('tutorial_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tutorial_video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tutorial_video_id')->constrained('tutorial_videos')->cascadeOnDelete();
            $table->integer('watch_time_seconds')->default(0);
            $table->timestamps();
        });

        Schema::create('tutorial_video_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tutorial_video_id')->constrained('tutorial_videos')->cascadeOnDelete();
            $table->text('komentar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorial_video_comments');
        Schema::dropIfExists('tutorial_video_views');
        Schema::dropIfExists('tutorial_videos');
    }
};
