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
        // 1. Upgrade point_histories table
        Schema::table('point_histories', function (Blueprint $table) {
            $table->renameColumn('deskripsi', 'activity_type');
        });
        
        Schema::table('point_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('activity_type');
            $table->string('reference_type')->nullable()->after('reference_id');
        });

        // 2. Add DB Triggers to point_histories
        DB::unprepared("
            CREATE TRIGGER prevent_point_histories_update
            BEFORE UPDATE ON point_histories
            FOR EACH ROW
            BEGIN
                INSERT INTO security_logs (event_type, table_name, description) 
                VALUES ('UNAUTHORIZED_UPDATE', 'point_histories', CONCAT('Attempted update on ID: ', OLD.id));
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Update on point_histories is forbidden';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER prevent_point_histories_delete
            BEFORE DELETE ON point_histories
            FOR EACH ROW
            BEGIN
                INSERT INTO security_logs (event_type, table_name, description) 
                VALUES ('UNAUTHORIZED_DELETE', 'point_histories', CONCAT('Attempted delete on ID: ', OLD.id));
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete on point_histories is forbidden';
            END
        ");

        // 3. Drop point_ledgers table (if exists)
        Schema::dropIfExists('point_ledgers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_point_histories_update");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_point_histories_delete");

        Schema::table('point_histories', function (Blueprint $table) {
            $table->renameColumn('activity_type', 'deskripsi');
            $table->dropColumn(['reference_id', 'reference_type']);
        });

        // We don't necessarily recreate point_ledgers in down() unless needed,
        // but for safety, we could just leave it out or recreate it empty.
    }
};
