<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    protected $primaryKey = 'role_id';
    protected $table = 'roles';

    protected $fillable = ['role_name', 'guard_name'];
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id')
                    ->withPivot('role_permission_id')
                    ->withTimestamps();
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id')
                    ->withPivot('role_user_id');
    }
    
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }
        
        return !!$permission->intersect($this->permissions)->count();
    }

}
