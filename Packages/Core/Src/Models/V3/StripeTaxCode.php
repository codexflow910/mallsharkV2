<?php

namespace Packages\Core\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Packages\Core\Models\V3\StateCategoryTax;
class StripeTaxCode extends Model
{
    protected $fillable = [
        'stripe_id',
        'name',
        'description',
    ];

}
