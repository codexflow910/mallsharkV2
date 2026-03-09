<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminReview extends Model
{
    protected $table = 'superadmin_reviews'; // Your actual table name
    protected $primaryKey = 'review_id'; // The primary key column
    
    use HasFactory;
}
