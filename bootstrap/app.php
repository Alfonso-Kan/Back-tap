<?php

use App\Http\Middleware\EnsureSeccionAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Railway (y la mayoria de PaaS) terminan TLS en su proxy y reenvian
        // HTTP puro al contenedor; sin esto Laravel genera URLs de assets/
        // rutas en http:// aunque el sitio se sirva en https://.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'seccion' => EnsureSeccionAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
