<?php

namespace Packages\Auth\Models\V2;

use Illuminate\Database\Eloquent\Model;

class CustomerWallet extends Model
{
    protected $table = 'customer_wallets';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'currency',
        'wallet_address',
    ];

    public $timestamps = true;

    // Relationships
    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'user_id', 'user_id');
    }
}