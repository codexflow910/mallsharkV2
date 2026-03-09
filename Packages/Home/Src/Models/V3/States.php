<?php

namespace Packages\Home\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Packages\Core\Models\V3\StateCategoryTax;
class States extends Model {

    protected $primaryKey = 'state_id';
    protected $table = 'states';

    public function stateCategoryTaxes()
    {
        return $this->hasMany(StateCategoryTax::class, 'state_id', 'state_id');
    }
}
