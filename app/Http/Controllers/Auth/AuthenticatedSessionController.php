<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller {
    
    /**
     * Display the login view.
     */
    public function create(): View {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse {
        $request->authenticate();
        if (isset(Auth::user()->role_id) && Auth::user()->role_id == '10') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => trans('Your account not a front-end user accoount.'),
            ]);
        }

        $request->session()->regenerate();
        if(Auth::user()->is_donate == '1'){
            return redirect()->intended(RouteServiceProvider::HOME);
        }else{
            //echo Auth::user()->status;die;
             if(Auth::user()->status == '1'){
                 return redirect()->route('newsfeed');
            }else{
                  Auth::guard('web')->logout();
                     $request->session()->invalidate();
                      throw ValidationException::withMessages([
                        'email' => trans('Your account has not been activated from the admin side.'),
                    ]);
               }
        }        
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
