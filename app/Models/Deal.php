<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'deals';

    protected $fillable = [
        'first_name','last_name','email','mobile','customer_type',
        'model_number', 'serial_number', 'material_watch', 'dial', 'condition', 'year', 'full_set', 'purchase_price', 'sale_price', 'delivery_cost',
        'country', 'state', 'city', 'address', 'zipcode', 'deal_status', 'status', 'brand_id',
        'gst_code', 'deal_supplier_status', 'purchase_invoice_number', 'purchase_invoice_date', 'purchase_currency', 'note',
        'review_status', 'reviewed_by', 'reviewed_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_to_purchase_rate' => 'decimal:8',
        'sale_in_purchase_currency' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'is_loss' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function dealBuyerDetail()
    {
        return $this->hasOne('App\Models\DealBuyerDetail', 'deal_id', 'id');
    }

    public function watchBrandDetail()
    {
        return $this->belongsTo('App\Models\WatchBrand', 'brand_id', 'id');
    }

    public function dealCustomerTypeDetail()
    {
        return $this->hasOne('App\Models\DealCustomerType', 'deal_id', 'id');
    }
    public function images()
    {
        return $this->hasMany('App\Models\DealImage', 'deal_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}