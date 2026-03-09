<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;

class ProductVisitor extends Model
{
    protected $table = 'product_visitors';

    protected $fillable = [
        'store_id',
        'product_id',
        'visitor_id',
        'visit_date',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];
}
