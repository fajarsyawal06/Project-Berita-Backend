<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tutorial_video_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tutorial_video_id')->constrained('tutorial_videos')->onDelete('cascade');
            $table->enum('event_type', ['play', 'pause', 'seek', 'complete']);
            $table->unsignedInteger('position_seconds');
            $table->timestamps();
        });

        // Add append-only DB Triggers
        DB::unprepared("
            CREATE TRIGGER prevent_tutorial_video_interactions_update
            BEFORE UPDATE ON tutorial_video_interactions
            FOR EACH ROW
            BEGIN
                INSERT INTO security_logs (event_type, table_name, description, created_at) 
                VALUES ('UNAUTHORIZED_UPDATE', 'tutorial_video_interactions', CONCAT('Attempted update on ID: ', OLD.id), NOW());
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Update on tutorial_video_interactions is forbidden';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER prevent_tutorial_video_interactions_delete
            BEFORE DELETE ON tutorial_video_interactions
            FOR EACH ROW
            BEGIN
                INSERT INTO security_logs (event_type, table_name, description, created_at) 
                VALUES ('UNAUTHORIZED_DELETE', 'tutorial_video_interactions', CONCAT('Attempted delete on ID: ', OLD.id), NOW());
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete on tutorial_video_interactions is forbidden';
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_tutorial_video_interactions_update");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_tutorial_video_interactions_delete");
        Schema::dropIfExists('tutorial_video_interactions');
    }
};
