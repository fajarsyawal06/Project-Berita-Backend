<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal untuk Highlight Pemenang Semester (FR-PG-04)
// Berjalan otomatis pada 1 Juli (untuk merangkum Semester 1)
Schedule::command('app:generate-season-winners')->cron('0 0 1 7 *');

// Berjalan otomatis pada 1 Januari (untuk merangkum Semester 2)
Schedule::command('app:generate-season-winners')->cron('0 0 1 1 *');
