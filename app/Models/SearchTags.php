<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchTags extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','tag', 'trending', 'key','ip_address'];
    protected $table = 'search_tags';
}
