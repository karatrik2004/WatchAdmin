<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\SanitizeHelper;

class SanitizeInputMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        $sanitized = $this->sanitizeArray($request->all());

        $request->merge($sanitized);

        return $next($request);
    }

    /**
     * Recursively sanitize an array of inputs.
     */
    private function sanitizeArray(array $inputs): array
    {
        foreach ($inputs as $key => &$value) {
            if (is_array($value)) {
                // If it's an array, sanitize recursively
                $value = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // If it's a string, apply sanitization
                $value = SanitizeHelper::sanitize($key, $value);
            }
        }
        return $inputs;
    }

}
