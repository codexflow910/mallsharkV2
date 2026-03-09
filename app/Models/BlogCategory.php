<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    use HasFactory;
    protected $table='blog_categories';

    protected $fillable = [
        'category_name',
        'category_slug',
        'parent_id',
        'status',
    ];
    
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_category');
    }
    
}
