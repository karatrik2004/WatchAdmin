<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Input;
use Redirect;
use Session;
use Artisan;
use Config;

use App\Models\Deal;
use App\Models\City;

class DealController extends Controller
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

    public function index(Request $request)
    {

        $input          = $request->all();
        $search         = (object) null;
        $arr['search']  = $search;
        $showrecord     = trans('admin.ADMIN_PAGE_LIMIT_NO');
        $deals          = new Deal();

        if (isset($input['search_text']) && $input['search_text'] != '') {
            $search_text            = $input['search_text'];
            $deals                  = $deals->where('model_number', 'LIKE', "%$search_text%");
            $search->search_text    = $search_text;
        }

        if (isset($input['deal_status']) && $input['deal_status'] != '') {
            $deal_status            = $input['deal_status'];
            $deals                  = $deals->where("deal_status", $deal_status);
            $search->deal_status    = $deal_status;
        }

        if (isset($input['showrecord']) && $input['showrecord'] != '') {
            $showrecord         = $input['showrecord'];
            $search->showrecord = $showrecord;
            Session::put('showrecord', $showrecord);
        }
        // filters
        $filters = $request->all();
        unset($filters['_token']);
        $arr['filters'] = $filters;

        $deals = $deals->orderBy('id','desc')->paginate($showrecord);
        return view('admin.deals.index', compact('deals'))->with($arr)
            ->with('i', ($request->input('page', 1) - 1) * $showrecord);
    }

    public function create()
    {
        $countries  = $this->getCountires();
        $states     = $this->getStates();
        $cities     = $this->getCities('1');
        
        return View::make("admin.deals.add", compact('countries','states','cities'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'model_number'  => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:deals,serial_number',
            'material_watch'=> 'required|string|max:255',
            'condition'     => 'required|string', // Example values
            'year'          => 'required|integer|min:1900|max:' . date('Y'),
            'full_set'      => 'required',
            'purchase_price'=> 'required|numeric|min:0',
            'sale_price'    => 'required|numeric|min:0', // Offer price should be less than cost price
            //'sale_price'    => 'required|numeric|min:0|lt:purchase_price', // Offer price should be less than cost price
        ]);
        

        $data =  $request->all();
        $deal = Deal::create($data);
        return redirect()->route("admin.deals.edit",$deal->id)->with('alert-success', 'Deal has been created successfully');
    }

    public function show($id)
    {
        $deal = Deal::find($id);
        if (empty($deal)) {
            return redirect()->route('admin.deals.index')->with('alert-error', 'Deal not found');
        }
        $countries  = $this->getCountires();
        $states     = $this->getStates();
        $cities     = $this->getCities($deal->state_id);
        return view('admin.deals.view', compact('deal','countries','states','cities'));
    }

    public function edit($id)
    {
        $deal = Deal::find($id);
        if (empty($deal)) {
            return redirect()->route('admin.deals.index')->with('alert-error', 'Deal not found');
        }

        $countries  = $this->getCountires();
        $states     = $this->getStates();
        $cities     = $this->getCities($deal->state_id);

        return view('admin.deals.edit', compact('deal','countries','states','cities'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $this->validate($request, [
            'model_number'  => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:deals,serial_number,' . $id,
            'material_watch'=> 'required|string|max:255',
            'condition'     => 'required|string', // Example values
            'year'          => 'required|integer|min:1900|max:' . date('Y'),
            'full_set'      => 'required',
            'purchase_price'=> 'required|numeric|min:0',
            'sale_price'    => 'required|numeric|min:0', // Offer price should be less than cost price
            'state_id'      => 'required',
            'city_id'       => 'required',            
            'address'       => 'required|string|max:255',
            'zipcode'       => 'required',
        ]);
        
        $deal = Deal::find($id);
        $deal->update($data);

        return redirect()->route('admin.deals.show',$deal->id)->with('alert-success', 'Deal has been updated successfully');
    }

    public function destroy($id)
    {
        $deal = Deal::find($id);
        $deal->delete();
        return redirect()->route('admin.deals.index')->with('alert-success', 'Deal has been deleted successfully');
    }

   
    public function downloadProductExcel(Request $request)
    {

        $input          = $request->all();
        $deals          = new Deal();

        if (isset($input['search_text']) && $input['search_text'] != '') {
            $search_text  = $input['search_text'];
            $deals     = $deals->where('model_number', 'LIKE', "%$search_text%");        
        }
        $deals = $deals->orderBy('id','desc')->get();

        
        
        $time      = date('dmY');
        $fileName  = 'deals_'.$time.'.csv';      
        $headers   = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns =  [
            'ID', 
            'Model Number', 
            'Serial Number',
            'Material of the watch',
            'Condition',
            'Year',
            'Full set or not',
            'Purchase Price',
            'Sale Price',
            'Country',
            'State',
            'City',
            'Address',
            'Zipcode',
            'Deal Status'

        ];
        
        $callback = function() use($deals, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);        
            if(!empty($deals)){     
                $countries   = $this->getCountires();
                $states      = $this->getStates();
                $deal_status = Config::get('constants.DEAL_STATUS');       
                foreach ($deals as $deal) {
                    $cities     = $this->getCities($deal->state_id);
                    fputcsv($file, [
                        $deal['id'],
                        $deal['model_number'],
                        $deal['serial_number'],
                        $deal['material_watch'],                        
                        $deal['condition'],
                        $deal['year'],
                        $deal['full_set'],
                        $deal['purchase_price'],
                        $deal['sale_price'],
                        (!empty($deal['country_id']) && isset($countries[$deal['country_id']]))?$countries[$deal['country_id']]:'',                        
                        (!empty($deal['state_id']) && isset($states[$deal['state_id']]))?$states[$deal['state_id']]:'',
                        (!empty($deal['city_id']) && isset($cities[$deal['city_id']]))?$cities[$deal['city_id']]:'',                        
                        $deal['address'],
                        $deal['zipcode'],
                        $deal_status[$deal['deal_status']]
                    ]);                    
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getCitiesByStateID($state_id)
    {        
        $cities = City::where(['status'=>'1','state_id'=>$state_id])->orderBy('city_name','asc')->pluck('city_name', 'id');
        return response()->json($cities);
    }
}