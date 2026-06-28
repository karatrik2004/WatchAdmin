<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WatchBrand extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'watch_brands';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
