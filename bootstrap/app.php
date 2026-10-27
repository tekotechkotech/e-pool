<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Behind Cloudflare Tunnel the origin is plain HTTP; trust all proxies
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'pengajuan.access' => \App\Http\Middleware\PengajuanAccessMiddleware::class,
            'feature.toggle' => \App\Http\Middleware\FeatureToggleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('pengajuan/umum/person*') || $request->is('pengajuan/umum/lembaga*') || $request->expectsJson(),
        );
    })->create();
