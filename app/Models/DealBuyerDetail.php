<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealBuyerDetail extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'deal_buyer_details';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function dealDetail()
    {
        return $this->belongsTo('App\Models\Deal', 'deal_id', 'id');
    }

    // Relationship with Country
    public function country()
    {
        return $this->belongsTo(Country::class, 'buyer_country_id');
    }

    // Relationship with State
    public function state()
    {
        return $this->belongsTo(State::class, 'buyer_state_id');
    }

    // Relationship with City
    public function city()
    {
        return $this->belongsTo(City::class, 'buyer_city_id');
    }

}
