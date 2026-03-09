<?php

namespace Packages\Distributors\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'url'];

    // 1 brand -> many brand_distributor records
    public function brandDistributors()
    {
        return $this->hasMany(BrandDistributor::class, 'brand_id');
    }

    // 1 brand -> many stores through pivot
    public function distributors()
    {
        return $this->belongsToMany(Store::class, 'brand_distributors', 'brand_id', 'distributor_id');
    }
}

