<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Correct CSP format: Ensure it is a single line without line breaks
        $csp = "default-src 'self';
         script-src 'self' 'unsafe-inline' https://apis.google.com https://maps.googleapis.com https://maps.gstatic.com;
        style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
        font-src 'self' data: https://fonts.gstatic.com;
        img-src 'self' data: https://maps.gstatic.com https://maps.googleapis.com;
        connect-src 'self' https://maps.googleapis.com https://maps.gstatic.com;";


        // Remove unintended new lines & extra spaces
        $csp = preg_replace('/\s+/', ' ', trim($csp));
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}


