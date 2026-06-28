<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealCustomerType extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'deal_company_details';


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
        return $this->belongsTo(Country::class, 'company_country_id');
    }

    // Relationship with State
    public function state()
    {
        return $this->belongsTo(State::class, 'company_state_id');
    }

    // Relationship with City
    public function city()
    {
        return $this->belongsTo(City::class, 'company_city_id');
    }

}
