<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
class DonationStatus
{
    public function handle($request, Closure $next)
    {       
        if (Auth::check() && Auth::user()->is_donation == '1'){          
          return $next($request);
        } else {
          return redirect()->route('donate');
        }       
        return $next($request);
    }
}
