<?php

namespace App\Http\Middleware;

use Closure;

class HSTS
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the response from the next middleware
        $response = $next($request);

        // Add the Strict-Transport-Security header to the response
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        return $response;
    }
}

