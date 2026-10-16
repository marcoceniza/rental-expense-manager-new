<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('generate-recurring')->dailyAt('00:01');

// Monthly transaction report sent on the last day of each month at 23:55 Asia/Manila time
Schedule::command('send-monthly-transaction-report')
    ->lastDayOfMonth('23:55')
    ->timezone('Asia/Manila');
