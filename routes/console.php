<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Send jersey colour submission reminders (5 and 3 days before each match) daily at 9am.
Schedule::command('jerseys:send-reminders')->dailyAt('09:00');
