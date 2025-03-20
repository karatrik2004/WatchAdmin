<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\ThrottlesLogins;

use App\Models\User;

use Auth;
use Config;
use DB;
use Validator;
use Input;
use Session;
use Image;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $total_orders               =  0;
        $total_pending_orders       =  0;
        $total_completed_orders     =  0;
        $total_today_orders         =  0;
       
        return view('admin.dashboard.index', compact('total_orders', 'total_pending_orders', 'total_completed_orders', 'total_today_orders'));
    }

    /**
     * Show the profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        $current_user = Auth::guard('admin')->user();
        return view("admin.dashboard.profile", compact('current_user'));
    }

    public function submit_profile(Request $request)
    {
        $data           =  $request->all();
        $current_user   =  Auth::guard('admin')->user();
        $arraydata      = array();
        $arraydata['firstname']         = $data['firstname'];
        $arraydata['lastname']          = $data['lastname'];
        $arraydata['mobile']            = $data['mobile'];  
        User::where('id', $current_user->id)->update($arraydata);
        return redirect()->back()->with('alert-success', 'Profile successfully updated');
    }

    public function changePassword()
    {
        return view('admin.dashboard.changepassword');
    }
    public function UpdatePassword(Request $request)
    {
        $data =  $request->all();
        $userArray = array();
        if (trim($data['password']) != "") {
            if (Auth::guard('admin')->check()) {
                $id = Auth::guard('admin')->user()->id;
            }
            $userArray['password'] = bcrypt($data['password']);
            User::where('id', $id)->update($userArray);

            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('alert-success', 'Password successfully updated');
        }
        return back();
    }
}
