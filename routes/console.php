<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:contracts-expiring')->dailyAt('08:00');
Schedule::command('notifications:dispatch-scheduled')->everyMinute();
Schedule::command('leave:accrue-monthly')->dailyAt(config('leave.accrual_schedule_time', '23:30'));
Schedule::command('leave:carry-over-annual')->yearlyOn(1, 1, '00:15');
