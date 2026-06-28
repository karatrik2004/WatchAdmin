<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealShipStationOrder extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'deal_ship_station_orders';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
