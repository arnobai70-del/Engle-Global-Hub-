<?php

use App\Models\ApiToken;
use App\Services\Admin\LaunchChecklist;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Housekeeping. Requires the server cron entry:
 * * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::call(fn () => ApiToken::query()->where('expires_at', '<', now())->delete())
    ->daily()->name('purge-expired-api-tokens')->withoutOverlapping();

Schedule::command('queue:prune-failed --hours=720')->weekly();

Schedule::command('auth:clear-resets')->daily();

// Heartbeat for Admin › Website launch checklist, which warns when the scheduler stops running.
Schedule::call(fn () => Cache::forever(LaunchChecklist::SCHEDULER_HEARTBEAT, now()->getTimestamp()))
    ->everyMinute()->name('scheduler-heartbeat');
