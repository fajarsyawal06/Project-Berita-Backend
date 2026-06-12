<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Triggers for security_logs
        DB::unprepared('DROP TRIGGER IF EXISTS tr_security_logs_prevent_update');
        DB::unprepared('
            CREATE TRIGGER tr_security_logs_prevent_update BEFORE UPDATE ON security_logs
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Updates are not allowed on append-only log table: security_logs";
            END;
        ');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_security_logs_prevent_delete');
        DB::unprepared('
            CREATE TRIGGER tr_security_logs_prevent_delete BEFORE DELETE ON security_logs
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Deletions are not allowed on append-only log table: security_logs";
            END;
        ');

        // 2. Triggers for news_status_logs
        DB::unprepared('DROP TRIGGER IF EXISTS tr_news_status_logs_prevent_update');
        DB::unprepared('
            CREATE TRIGGER tr_news_status_logs_prevent_update BEFORE UPDATE ON news_status_logs
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Updates are not allowed on append-only log table: news_status_logs";
            END;
        ');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_news_status_logs_prevent_delete');
        DB::unprepared('
            CREATE TRIGGER tr_news_status_logs_prevent_delete BEFORE DELETE ON news_status_logs
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Deletions are not allowed on append-only log table: news_status_logs";
            END;
        ');

        // 3. Triggers for point_histories
        DB::unprepared('DROP TRIGGER IF EXISTS tr_point_histories_prevent_update');
        DB::unprepared('
            CREATE TRIGGER tr_point_histories_prevent_update BEFORE UPDATE ON point_histories
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Updates are not allowed on append-only log table: point_histories";
            END;
        ');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_point_histories_prevent_delete');
        DB::unprepared('
            CREATE TRIGGER tr_point_histories_prevent_delete BEFORE DELETE ON point_histories
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Deletions are not allowed on append-only log table: point_histories";
            END;
        ');

        // 4. Stored Procedures
        DB::unprepared('DROP PROCEDURE IF EXISTS SP_InsertSecurityLog');
        DB::unprepared('
            CREATE PROCEDURE SP_InsertSecurityLog(
                IN p_event_type VARCHAR(255),
                IN p_table_name VARCHAR(255),
                IN p_description TEXT
            )
            BEGIN
                INSERT INTO security_logs (event_type, table_name, description, created_at)
                VALUES (p_event_type, p_table_name, p_description, NOW());
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_security_logs_prevent_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_security_logs_prevent_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_news_status_logs_prevent_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_news_status_logs_prevent_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_point_histories_prevent_update');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_point_histories_prevent_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS SP_InsertSecurityLog');
    }
};
