<?php

namespace Packages\Auth\Models\V4;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Packages\Home\Models\V4\{States,ShippingAddress,Countries};
use Packages\Order\Models\V4\StoreOrder;
use Packages\Store\Models\V4\Store;
use Packages\Auth\Models\V4\DeviceToken;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_no',
        'user_country_id',
        'user_state_id',
        'user_zip_code',
        'user_city_id',
        'physical_address',
        'about',
        'apartment_type',
        'apartment_number',
        'status',
        'forget_key',
        'guest_user',
        'register_source'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function shipping_address()
    {
        return $this->hasOne(ShippingAddress::class, 'user_id', 'user_id')->with('country');
    }
    public function buyerOrders()
    {
        return $this->hasMany(StoreOrder::class, 'buyer_id', 'user_id');
    }
    public function get_states()
    {
        return $this->belongsTo(States::class, 'user_state_id', 'state_id');
    }
    public function device_token()
    {
        return $this->hasMany(DeviceToken::class, 'user_id', 'user_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class, 'user_id', 'user_id');
    }
    public function state() {
        return $this->belongsTo(States::class, 'user_state_id', 'state_id');
    }
    public function country() {
        return $this->belongsTo(Countries::class, 'user_country_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
                    ->withPivot('role_user_id');
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('role_name', $role);
        }
        
        return !!$role->intersect($this->roles)->count();
    }

    public function hasPermission($permission)
    {
        // First check direct permissions
        if (is_string($permission)) {
            if ($this->permissions->contains('name', $permission)) {
                return true;
            }
        } else {
            if ($permission->intersect($this->permissions)->count() > 0) {
                return true;
            }
        }

        // Then check through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id' )->wherePivot('model_type', self::class);
    }

    public function getAllPermissions()
    {
        // Get role-based permissions
        $rolePermissions = $this->roles()
            ->with('permissions') // eager load
            ->get()
            ->pluck('permissions')
            ->flatten();

        // Get direct permissions
        $directPermissions = $this->permissions;

        // Merge, unique by name, and return only names
        return $rolePermissions
            ->merge($directPermissions)
            ->unique('name')
            ->pluck('name')
            ->values();
    }
}
