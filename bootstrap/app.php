<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->encryptCookies(except: [
            'appearance',
            'sidebar_state',
        ]);

        // ✅ CSRF bypass correto no Laravel 12
        $middleware->validateCsrfTokens(except: [
            '/ai/chat',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'tenant' => \App\Http\Middleware\EnsureTenant::class,
        ]);

        $middleware->appendToGroup('auth', [
            SetTenant::class,
        ]);

        $middleware->web(append: [
            SetTenant::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('ai:follow-ups')->dailyAt('09:00');
        $schedule->command('deals:check-inactive')->dailyAt('10:00');
        $schedule->command('deals:send-follow-ups')->hourly();
    })
    ->create();
