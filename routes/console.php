<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Contribution reminders: daily at midday (12:00).
// For daily groups, all members are emailed every day.
// For weekly/monthly groups, members are emailed on the first and last day of the contribution period.
Schedule::command('contribution:email-reminder')->dailyAt('12:00');
