<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
	use HasFactory;
    use Sortable;
  
    protected $table = 'cities';

    protected $fillable = [
        'country_id','state_id','city_name','city_lat','city_lng','status'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];    
    
}