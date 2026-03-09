<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $primaryKey = 'permission_id';
    protected $fillable = ['name', 'display_name', 'group_name', 'gaurd_name'];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id')
                    ->withPivot('role_permission_id')
                    ->withTimestamps();
    }
}
