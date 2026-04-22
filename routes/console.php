<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('osem:poll-orders --limit=100')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('osem:sync-services --markup=20')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
