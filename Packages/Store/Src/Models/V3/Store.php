<?php

namespace Packages\Store\Models\V3;

use Packages\Auth\Models\V3\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Packages\Product\Models\V3\Product;
use Packages\Order\Models\V3\StoreOrder;
use Packages\Store\Models\V3\States;
use Packages\Store\Models\V3\StoreShippingOrigin;

class Store extends Model
{
    use Searchable;
    use SoftDeletes;

    protected $primaryKey = 'store_id';
    protected $table = 'stores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'paypal_merchant_id',
        'paypal_tracking_id',
        'paypal_connected',
        'paypal_account_status',
        'paypal_permissions_granted',
        'paypal_onboarding_link',
        'paypal_payments_receivable',
        'paypal_primary_email_confirmed',
        'paypal_status_checked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'paypal_permissions_granted' => 'array',
        'paypal_payments_receivable' => 'boolean',
        'paypal_primary_email_confirmed' => 'boolean',
        'paypal_status_checked_at' => 'datetime',
    ];

    public function searchableAs()
    {
        return env('STORE_INDEX', 'stores');
    }

    public function getScoutKey()
    {
        return $this->store_id;
    }

    public function getScoutKeyName()
    {
        return 'store_id';
    }

    public function toSearchableArray()
    {
        if ($this->store_status != 2) {
            return [];
        }

        return [
            'store_id' => $this->store_id,
            'store_name' => $this->store_name,
            'store_slug' => $this->store_slug,
            'store_logo' => $this->store_logo ? getAttachmentUrlById((int) $this->store_logo, 'avatar') : null,
        ];
    }

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }
    public function products() {
        return $this->hasMany(Product::class, 'store_id', 'store_id');
    }

    public function cartItems()
    {
        return $this->hasMany(ProductsCart::class);
    }
    public function storeOrders()
    {
        return $this->hasMany(StoreOrder::class, 'store_id', 'store_id');
    }

    public function state()
    {
        return $this->belongsTo(States::class, 'store_state', 'state_id');
    }
    public function country()
    {
        return $this->belongsTo(Countries::class, 'store_country','id');
    }
    public function storeShippingOrigins()
    {
        return $this->hasMany(StoreShippingOrigin::class, 'store_id');
    }
}
