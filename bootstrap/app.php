<?php

use App\Http\Middleware\CookieJwt;
use App\Http\Middleware\EnsureStaffHasRole;
use App\Http\Middleware\EnsureStoreScope;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies('*');
        $middleware->alias([
            'role' => EnsureStaffHasRole::class,
            'store.scope' => EnsureStoreScope::class,
            'cookie.jwt' => CookieJwt::class,
        ]);

        // JWT cookie: Ä‘á»c cookie jwt_token -> Authorization Bearer cho api.
        $middleware->prependToGroup('api', CookieJwt::class);

        // API khÃ´ng cÃ³ trang login: tráº£ 401 JSON thay vÃ¬ redirect.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/login'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
