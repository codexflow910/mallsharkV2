<?php

namespace Packages\Core\Models\V2;

use Illuminate\Database\Eloquent\Model;

class StripeTaxCode extends Model
{
    protected $table = 'tax_categories';

    protected $fillable = [
        'stripe_tax_id',
        'name',
    ];
}
