<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

use Mail;
use Config;
use Auth;
use Exception;
use Log;
use DB;
use Psl\Type\Internal\MixedDictType;

use File;
use Saloon\XmlWrangler\XmlWriter;

use App\Models\Country;
use App\Models\City;
use App\Models\State;


class Controller extends BaseController {
    use AuthorizesRequests, ValidatesRequests;   

    function getCountires(){
        //$list_arr = Country::where(['status'=>'1'])->orderBy('name','asc')->pluck('name', 'id')->prepend("Please Select Country","");
        $list_arr = Country::where(['status'=>'1'])->orderBy('name','asc')->pluck('name', 'id');
        return $list_arr;
    }

    function getStates(){        
        $list_arr = State::where(['status'=>'1','country_id'=>'13'])->orderBy('state_name','asc')->pluck('state_name', 'id')->prepend("Select State","");
        return $list_arr;
    }

    function getCities($state_id){        
        $list_arr = City::where(['status'=>'1','state_id'=>$state_id])->orderBy('city_name','asc')->pluck('city_name', 'id')->prepend("Select City","");
        return $list_arr;
    }
}