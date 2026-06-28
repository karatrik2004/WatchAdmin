<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Carbon\Carbon;

use Notification;
use App\Notifications\ResetPassword;

use App\Models\User;


use Auth;
use Config;
use DB;
use Validator;
use Input;
use Session;
use Image;
use Cookie;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('guestAdmin')->except('logout');
    }

    public function login(Request $request)
    {
        if (Auth::guard('admin')->user()) {
            return redirect()->route('admin.dashboard');
        }

        $arr = array();
        $input = $request->all();
        $arr['return_url'] = (isset($input['return_url']) && !empty($input['return_url'])) ? $input['return_url'] : '';
        return view('admin.login')->with($arr);
    }

    //loginProcess for login check
    public function loginProcess(Request $request)
    {
        $input = $request->all();
        $validator = validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required']
        ]);

        $remember_me = $request->has('remember') ? true : false;

        if ($request->has('remember')) {
            Cookie::queue('frontemail', $request->email, 1440);
            Cookie::queue('frontpassword', $request->password, 1440);
        } else {
            Cookie::queue('frontemail', $request->email, -1440);
            Cookie::queue('frontpassword', $request->password, -1440);
        }

        if ($validator->fails()) {
            return redirect()->route('admin.login')->withErrors($validator)->withInput();
        } else {
            //check authentication of uname and password
            $userInfo = array("email" => $input['email'], "password" => $input['password']);

            if (Auth::guard('admin')->attempt($userInfo, $remember_me)) {
                $user = Auth::guard('admin')->user();

                if ($user->status != 1) {
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('alert-error', "Your account has been deactivated.")->withInput();
                }

                // Redirect based on role
                if ($user->role_id == 1) {
                    return redirect()->route('admin.dashboard'); // Super Admin Dashboard
                } elseif ($user->role_id == 2) {
                    return redirect()->route('admin.dashboard'); // Account Role Dashboard
                } else {
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('alert-error', "Unauthorized role.")->withInput();
                }
            }

            return redirect()->route('admin.login')->with('alert-error', "Email or password is incorrect.")->withInput();

        }
    }

    //logout for login user
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function forgotPassword()
    {
        if (Auth::guard('admin')->user()) {
            return redirect()->route('admin.dashboard');
        }
        $arr = array();
        return view('admin.forgotpassword')->with($arr);
    }
}
