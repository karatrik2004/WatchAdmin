<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
	use HasFactory;
    use Sortable;
  
    protected $table = 'countries';

    protected $fillable = [
        'shortname','name','phonecode','status'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];    
    
}