<?php

use App\Http\Middleware\CheckSingleSession;
use App\Http\Middleware\EnforceSimulacrosDomain;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\NoCache;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'XSRF-TOKEN']);

        // Excluir webhooks de pasarelas de pago y WATI de la verificación CSRF + login/register para evitar 419 en algunos navegadores/proxy
        $middleware->validateCsrfTokens(except: [
            '/payments/confirmation',
            '/payments/wompi/webhook',
            '/webhooks/wati',
            '/webhooks/wati/*',
            '/api/wati/*',
            '/logout',
            'login',
            'login/*',
            '/login',
            '/login/*',
            'register',
            'register/*',
            '/register',
            '/register/*',
            '/iniciar',
            '/guardar-progreso',
            '/enviar',
            '/simulacros/iniciar',
            '/simulacros/guardar-progreso',
            '/simulacros/enviar',
        ]);

        $middleware->alias([
            'admin' => IsAdmin::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnforceSimulacrosDomain::class,
            CheckSingleSession::class,
            NoCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
