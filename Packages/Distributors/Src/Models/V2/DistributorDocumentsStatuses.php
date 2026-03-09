<?php

namespace Packages\Distributors\Models\V2;

use Illuminate\Database\Eloquent\Model;

class DistributorDocumentsStatuses extends Model
{
    protected $fillable = [
        'distributor_document_id',
        'attachment_id',
        'status',
    ];

    public function distributorDocument()
    {
        return $this->belongsTo(DistributorDocument::class, 'distributor_document_id');
    }
}
