<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use SoftDeletes, HasFactory;
    use Sortable;

    protected $table = 'products';

    protected $fillable = [
        'name', 'brand', 'model_number', 'description', 'price', 'status',
    ];
}
