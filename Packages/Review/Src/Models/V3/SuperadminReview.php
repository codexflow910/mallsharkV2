<?php

namespace Packages\Review\Models\V3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminReview extends Model
{
    protected $table = 'superadmin_reviews'; // Your actual table name
    protected $primaryKey = 'review_id'; // The primary key column
    
    use HasFactory;

    protected $fillable = ['product_id', 'name', 'rating', 'comment', 'created_at', 'updated_at', 'user_id', 'status'];

}
