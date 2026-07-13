<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vendors';

    protected $fillable = [
        'first_name',
        'company_name',
        'vendor_type',
        'last_name',
        'email',
        'contact_number',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'abn',
        'dealer_licence_number',
        'director_name',
    ];
}