<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Deal;
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
        $totalDeals = Deal::count();

        $statusCounts = Deal::select('deal_status', \DB::raw('count(*) as total'))
            ->groupBy('deal_status')
            ->pluck('total', 'deal_status')
            ->toArray();


        $statusLabels = config('constants.SEARCH_DEAL_STATUS');
        $statusLabels = array_filter($statusLabels, function ($key) {
            return !empty($key); // This removes the key if it's an empty string
        }, ARRAY_FILTER_USE_KEY);
        $newStatusCount = [];
        foreach ($statusLabels as $s => $sLabel) {
            $newStatusCount[$s] = $statusCounts[$s] ?? "0";
        }
        $statusCounts = $newStatusCount;

        unset($statusLabels['']);

        $statusStyles = config('constants.SEARCH_DEAL_STATUS_STYLE');


        return view('admin.dashboard.index', compact('totalDeals', 'statusCounts', 'statusLabels', 'statusStyles'));


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
        $data = $request->all();
        $current_user = Auth::guard('admin')->user();
        $arraydata = array();
        $arraydata['firstname'] = $data['firstname'];
        $arraydata['lastname'] = $data['lastname'];
        $arraydata['mobile'] = $data['mobile'];
        User::where('id', $current_user->id)->update($arraydata);
        return redirect()->back()->with('alert-success', 'Profile successfully updated');
    }

    public function changePassword()
    {
        return view('admin.dashboard.changepassword');
    }

    public function UpdatePassword(Request $request)
    {
        $data = $request->all();
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
