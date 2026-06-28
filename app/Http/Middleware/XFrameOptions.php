<?php

namespace App\Http\Middleware;

use Closure;

class XFrameOptions
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

        // Add the X-Frame-Options header to the response
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');  // or 'DENY'

        return $response;
    }
}

