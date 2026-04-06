<?php

declare(strict_types=1);

use App\Http\Middleware\CheckFirstSetup;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\UniversalAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'universal.auth' => UniversalAuth::class,
            'admin'          => EnsureIsAdmin::class,
        ]);

        $middleware->web(prepend: [
            CheckFirstSetup::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
