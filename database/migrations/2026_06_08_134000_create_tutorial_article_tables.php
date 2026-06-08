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
        Schema::create('tutorial_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_category_id')->nullable()->constrained('tutorial_categories')->nullOnDelete();
            $table->string('judul');
            $table->longText('konten');
            $table->enum('status', ['Draft', 'Published'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('tutorial_article_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_article_id')->constrained('tutorial_articles')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorial_article_attachments');
        Schema::dropIfExists('tutorial_articles');
    }
};
