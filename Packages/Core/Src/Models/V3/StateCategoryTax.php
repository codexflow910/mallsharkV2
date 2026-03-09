<?php

namespace Packages\Core\Models\V3;

use Packages\Core\Models\V3\StripeTaxCode;
use Illuminate\Database\Eloquent\Model;
use Packages\Home\Models\V3\States;
class StateCategoryTax extends Model
{
    protected $table = 'state_category_taxes';

    protected $fillable = ['state_id','tax_category_id', 'mallshark_categories', 'rate', 'expires_at', 'postal_code'];
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