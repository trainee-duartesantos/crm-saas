<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

use App\Http\Middleware\SetTenant;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnsureTenant;
use App\Http\Middleware\RequireRole;

// 👇 ESTE IMPORT É O QUE FALTAVA
use App\Providers\AuthServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    // ✅ REGISTAR PROVIDERS (Laravel 12)
    ->withProviders([
        AuthServiceProvider::class,
    ])

    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->encryptCookies(except: [
            'appearance',
            'sidebar_state',
        ]);

        $middleware->validateCsrfTokens(except: [
            '/ai/chat',
        ]);

        $middleware->alias([
            'tenant' => EnsureTenant::class,
            'role' => RequireRole::class,
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
