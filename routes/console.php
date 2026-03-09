<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. Laravel will
| automatically execute these tasks based on the defined schedule.
|
*/

Schedule::command('discounts:update-status')->everyFiveMinutes();
Schedule::command('site:map')->dailyAt('00:00');
Schedule::command('algolia:batch-update')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('package:check-expired')->daily();
Schedule::command('send:low-stock-notifications')->daily();
Schedule::command('recommended:generate')->everyTenMinutes();
Schedule::command('shipstation:change-order-status')->everyMinute();
Schedule::command('ai:reset-quota')->monthlyOn(1, '00:00');
Schedule::command('slides:set-activate')->everyMinute();
Schedule::command('distributor:license-expired')->daily();
Schedule::command('products:algolia-syncing')->everyThirtyMinutes();
Schedule::command('orders:cleanup')->weekly();
Schedule::command('facebook:regenerate-feed')->everyTenMinutes();
Schedule::command('tax:delete-expired')->daily();
