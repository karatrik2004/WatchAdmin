<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {

        if (!$request || !$request->user('admin')->hasAnyRole($roles)) {
            abort(403, 'Unauthorized action.');
        }
        // Check if user has no roles (i.e., empty roles)
        if ($request->user('admin')->roles->isEmpty()) {
            abort(403, 'Unauthorized action. No roles assigned.');
        }
        return $next($request);
    }
}


