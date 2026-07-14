<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'buyer_name',
        'buyer_email',
        'buyer_address',
        'buyer_city',
        'buyer_state',
        'buyer_country',
        'buyer_zipcode',
    ];
}
