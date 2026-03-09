<?php

namespace Packages\Review\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    protected $primaryKey = 'review_id';
    protected $guarded = [];

    public function __construct()
    {

    }

}
