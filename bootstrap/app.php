<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

// Ensure .env exists before Laravel tries to load it
if (! file_exists(dirname(__DIR__) . '/.env') && file_exists(dirname(__DIR__) . '/.env.example')) {
    copy(dirname(__DIR__) . '/.env.example', dirname(__DIR__) . '/.env');
}

// Ensure .env exists before Laravel tries to load it
if (! file_exists(dirname(__DIR__) . '/.env') && file_exists(dirname(__DIR__) . '/.env.example')) {
    copy(dirname(__DIR__) . '/.env.example', dirname(__DIR__) . '/.env');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/portal.php'));
            Route::middleware('web')
                ->group(base_path('routes/student.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\WizardMiddleware::class);
        $middleware->alias([
            'module' => \App\Http\Middleware\ModuleAccessMiddleware::class,
            'customer.portal' => \App\Http\Middleware\CustomerPortalMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
