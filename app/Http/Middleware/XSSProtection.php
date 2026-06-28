<?php

namespace App\Http\Middleware;

use Closure;

class XSSProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Set the X-XSS-Protection header
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}

