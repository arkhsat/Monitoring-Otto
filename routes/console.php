<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Cronjob monitoring status jaringan (Online/Offline) per lokasi.
 * Berjalan otomatis setiap 1 menit dan mencatat riwayat ke log.
 */
Schedule::command('app:status-online-monitoring')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/cron-monitoring.log'));

