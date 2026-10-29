<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CookieJwt
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->bearerToken() && $cookie = $request->cookie('jwt_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $cookie);
        }

        return $next($request);
    }
}
