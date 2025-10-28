<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // 1. Add the 'cors' alias here
        $middleware->alias([
            'role' => RoleMiddleware::class,
            // 'cors' => \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // // 2. Define the 'api' middleware group to match the image
        // // Note: This overrides Laravel's default 'api' group, which usually includes throttling.
        // $middleware->group('api', [
        //     'cors',
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();