<?php

namespace Packages\Distributors\Models\V4;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{

    protected $primaryKey = 'store_id';
    protected $table = 'stores';

    public function brandDistributors()
    {
        return $this->hasMany(BrandDistributor::class, 'distributor_id', 'store_id');
    }

    public function documents()
    {
        return $this->hasMany(DistributorDocument::class, 'distributor_id', 'store_id');
    }
}
