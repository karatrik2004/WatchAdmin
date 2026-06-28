<?php

namespace App\Http\Middleware;

use Closure;

class ReferrerPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        return $response;
    }
}
