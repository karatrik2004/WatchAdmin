<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEmail extends Model
{
    protected $table = 'notification_emails';

    protected $fillable = [
        'type',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
