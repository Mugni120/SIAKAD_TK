<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            require __DIR__.'/../routes/legacy.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The legacy application expects raw POST values.
        $middleware->remove([
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Keep Laravel's configuration separate from the legacy config/ directory.
// Laravel loads every PHP file in its config path, so using the original
// config/ directory would execute config/koneksi.php on every framework boot.
$app->useConfigPath(__DIR__.'/../laravel-config');

return $app;
