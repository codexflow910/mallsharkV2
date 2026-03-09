<?php

namespace Packages\Core\Models\V4;

use Packages\Core\Models\V4\StripeTaxCode;
use Illuminate\Database\Eloquent\Model;
use Packages\Home\Models\V4\States;
class StateCategoryTax extends Model
{
    protected $table = 'state_category_taxes';

    protected $fillable = ['state_id', 'mallshark_categories', 'rate'];

    public function state()
    {
        return $this->belongsTo(States::class, 'state_id', 'state_id');
    }
    public function taxCategory()
    {
        return $this->belongsTo(StripeTaxCode::class, 'tax_category_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'mallshark_categories', 'id');
    }
}