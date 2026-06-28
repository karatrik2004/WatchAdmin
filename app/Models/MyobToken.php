<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyobToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];

    protected $dates = [
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];
}
