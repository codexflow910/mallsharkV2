<?php

namespace Packages\Distributors\Models\V4;

use Illuminate\Database\Eloquent\Model;

class DistributorDocument extends Model
{
    protected $fillable = [
        'brand_distributor_id',
        'brand_name',
        'brand_url',
        'distributor_logo',
        'documents',
        'expiry_date',
        'status',
        'distributor_id',
        'note',
    ];

    public function distributorDocumentsStatuses()
    {
        return $this->hasMany(DistributorDocumentsStatuses::class);
    }

    // belongs to the distributor (store)
    public function store()
    {
        return $this->belongsTo(Store::class, 'distributor_id', 'store_id');
    }

    // belongs to the pivot record
    public function brandDistributor()
    {
        return $this->belongsTo(BrandDistributor::class, 'brand_distributor_id', 'id');
    }
}

