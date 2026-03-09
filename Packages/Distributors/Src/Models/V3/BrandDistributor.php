<?php

namespace Packages\Distributors\Models\V3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandDistributor extends Model
{
    use HasFactory;

    protected $fillable = ['brand_id', 'distributor_id'];

    // Each pivot entry belongs to ONE distributor (store)
    public function distributor()
    {
        return $this->belongsTo(Store::class, 'distributor_id', 'store_id');
    }

    // Each pivot entry has many distributor documents
    public function distributorDocuments()
    {
        return $this->hasMany(DistributorDocument::class, 'brand_distributor_id', 'id');
    }

    // Optional: relation to Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}

