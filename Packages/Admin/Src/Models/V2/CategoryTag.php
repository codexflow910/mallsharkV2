<?php

namespace Packages\Admin\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTag extends Model
{
    use HasFactory;

    protected $table = 'category_tags';

    protected $fillable = [
        'name', 
        'category_id',
    ];
}
