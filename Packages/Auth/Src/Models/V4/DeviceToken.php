<?php

namespace Packages\Auth\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','fcm_token','app_flag'];
    protected $table = 'device_tokens';
}
