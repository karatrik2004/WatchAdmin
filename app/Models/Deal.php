<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
	use SoftDeletes,HasFactory;
    use Sortable;
  
    protected $table = 'deals';

    protected $fillable = [
        'model_number','serial_number','material_watch','condition','year','full_set','purchase_price','sale_price',
        'country_id','state_id','city_id','address','zipcode','deal_status','status'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];    
    
}