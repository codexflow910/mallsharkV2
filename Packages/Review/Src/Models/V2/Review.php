<?php

namespace Packages\Review\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Packages\Auth\Models\V2\User;

class Review extends Model
{

    protected $primaryKey = 'review_id';
    protected $guarded = [];

    public function __construct()
    {

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

}
