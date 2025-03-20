<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use DB;
use File;
use GuzzleHttp\Client;
use Psl\Comparison\Order;
use Saloon\XmlWrangler\Data\Element;


class PageController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Summary of index
    */
    public function index(Request $request) {	    
      return redirect()->route("admin.login");
    }
   
    
}