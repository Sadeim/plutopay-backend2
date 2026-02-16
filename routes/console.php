<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\RetryFailedWebhooks;

Schedule::job(new RetryFailedWebhooks)->everyMinute();

Schedule::command('terminals:sync-status')->everyFiveMinutes();

Schedule::command('merchants:daily-summary')->dailyAt('23:00');
