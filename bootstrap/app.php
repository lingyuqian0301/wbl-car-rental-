<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'staff' => \App\Http\Middleware\EnsureUserIsStaff::class,
            'adminOrStaff' => \App\Http\Middleware\EnsureUserIsAdminOrStaff::class,
            'runner' => \App\Http\Middleware\RunnerMiddleware::class,
        ]);
        
        // Exclude booking/finalize from CSRF verification (for debugging)
        $middleware->validateCsrfTokens(except: [
            'booking/finalize',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
