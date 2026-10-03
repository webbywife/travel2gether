<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fallback queue runner: works the queue (AI day drafts) from the scheduler
// cron, so drafts still run even before a dedicated Ploi queue daemon exists.
// Harmless alongside a daemon — it just finds the queue empty.
Schedule::command('queue:work --stop-when-empty --tries=1 --timeout=170 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping(5);
