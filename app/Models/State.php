<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
	use HasFactory;
    use Sortable;
  
    protected $table = 'states';

    protected $fillable = [
        'country_id','state_name','status'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];    
    
}