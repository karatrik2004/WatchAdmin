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

class StockController extends Controller
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
        // Get stock data grouped by brand_id, along with the brand names
        $brand_stocks = Deal::selectRaw('
        deals.brand_id,
        watch_brands.name AS brand_name,
        SUM(deals.purchase_price) AS total_bought_value,
        SUM(CASE WHEN deals.deal_status = 3 THEN deals.purchase_price ELSE 0 END) AS total_sold_value,
        (COUNT(*) - COUNT(CASE WHEN deals.deal_status = 3 THEN 1 END)) AS bought_stock,
        COUNT(CASE WHEN deals.deal_status = 3 THEN 1 END) AS sold_stock,
        (SUM(deals.purchase_price) - SUM(CASE WHEN deals.deal_status = 3 THEN deals.purchase_price ELSE 0 END)) AS value_on_hand
    ')
            ->leftJoin('watch_brands', 'watch_brands.id', '=', 'deals.brand_id')
            ->whereNotNull('deals.brand_id')
            ->groupBy('deals.brand_id', 'watch_brands.name')
            ->orderBy('watch_brands.name', 'ASC')
            ->get();


        $state_stocks = Deal::selectRaw('
        COALESCE(
            deals.state_id,
            deal_company_details.company_state_id
        ) AS state_id,
        states.state_name AS state_name,
        SUM(deals.purchase_price) AS total_bought_value,
        SUM(CASE WHEN deals.deal_status = 3 THEN deals.purchase_price ELSE 0 END) AS total_sold_value,
        (COUNT(*) - COUNT(CASE WHEN deals.deal_status = 3 THEN 1 END)) AS bought_stock,
        COUNT(CASE WHEN deals.deal_status = 3 THEN 1 END) AS sold_stock,
        (SUM(deals.purchase_price) - SUM(CASE WHEN deals.deal_status = 3 THEN deals.purchase_price ELSE 0 END)) AS value_on_hand
    ')
            ->leftJoin('deal_company_details', function ($join) {
                $join->on('deal_company_details.deal_id', '=', 'deals.id')
                    ->where('deals.customer_type', 'company');
            })
            ->leftJoin('states', function ($join) {
                $join->on('states.id', '=', \DB::raw('COALESCE(deals.state_id, deal_company_details.company_state_id)'));
            })
            ->where(function ($query) {
                $query->whereNotNull('deals.state_id')
                    ->orWhereNotNull('deal_company_details.company_state_id');
            })
            ->groupByRaw('COALESCE(deals.state_id, deal_company_details.company_state_id), states.state_name')
            ->orderBy('states.state_name', 'ASC')
            ->get();





        return view('admin.stocks.index', compact('brand_stocks', 'state_stocks'));
    }


}
