<?php

namespace Packages\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutLog extends Model
{
    protected $fillable = [
        'event_type',
        'user_id',
        'session_id',
        'data',
    ];
}
