<?php
namespace App\Http\Middleware;

use Closure;
use Auth, Redirect;

class GuestAdmin {
    public function handle($request, Closure $next) {
        if (Auth::guard('admin')->check()) {          
            return redirect(route('admin.dashboard'));
        }
        return $next($request);
    }
}