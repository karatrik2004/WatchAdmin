<?php

namespace App\Http\Middleware;

use Closure;

class ContentTypeOptions
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

        // Add the X-Content-Type-Options header to the response
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
