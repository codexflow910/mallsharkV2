<?php

namespace Packages\Core\Models\V3;
use Illuminate\Database\Eloquent\Model;
class TaxCategory extends Model
{
    protected $table = 'tax_categories';

    protected $fillable = ['code', 'name', 'description'];

    public function stateCategoryTaxes()
    {
        return $this->hasMany(StateCategoryTax::class, 'tax_category_id', 'id');
    }
    public function categories()
    {
        return $this->hasMany(Category::class, 'tax_category_id');
    }
}