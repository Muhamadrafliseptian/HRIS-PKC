<?php

use App\Http\Middleware\ApiKeyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsMaintenance;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\UserActiveMiddleware;
use App\Http\Middleware\WhitelistApi;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\ForceHttps::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'maintenance' => IsMaintenance::class,
            'permission' => PermissionMiddleware::class,
            'super_admin' => SuperAdmin::class,
            'is_active' => UserActiveMiddleware::class,
            'auth.apikey' => ApiKeyMiddleware::class,
            'whitelist' => WhitelistApi::class,
        ]);
    })
    ->withExceptions(function (Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->inertia()) {
                return inertia('Auth/Index')
                    ->with(['message' => 'Session expired, please login again'])
                    ->toResponse($request)
                    ->setStatusCode(409);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
