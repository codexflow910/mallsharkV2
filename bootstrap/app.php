<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('discounts:update-status')->everyMinute();
        $schedule->command('site:map')->dailyAt('00:00');
        $schedule->command('algolia:batch-update')->everyMinute()->withoutOverlapping();
        $schedule->command('package:check-expired')->daily();
        $schedule->command('send:low-stock-notifications')->daily();
        $schedule->command('recommended:generate')->everyTenMinutes();
        $schedule->command('shipstation:change-order-status')->everyMinute();
        $schedule->command('ai:reset-quota')->monthlyOn(1, '00:00');
        $schedule->command('slides:set-activate')->everyMinute();
        $schedule->command('distributor:license-expired')->daily();
        $schedule->command('products:algolia-syncing')->everyThirtyMinutes();
        $schedule->command('orders:cleanup')->weekly();
        $schedule->command('facebook:regenerate-feed')->everyTenMinutes();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middleware
        $middleware->trustProxies(at: [
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
        ]);

        // Web Middleware Group
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API Middleware Group
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Middleware Aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'loggedin' => \App\Http\Middleware\LoggedIn::class,
            'StoreAccess' => \App\Http\Middleware\StoreAccess::class,
            'AdminAccess' => \App\Http\Middleware\AdminAccess::class,
            'SupportAccess' => \App\Http\Middleware\SupportAccess::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'checkRole' => \App\Http\Middleware\CheckRole::class,
            'userPermission' => \App\Http\Middleware\UserPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
