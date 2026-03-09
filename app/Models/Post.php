<?php

/**
 * Description of Post
 *
 * @author Noman
 */

namespace App\Models;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    protected $primaryKey = 'id';
    protected $table = 'posts';

    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'post_category', 'post_id', 'category_id');
    }

}