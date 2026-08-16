<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    
    protected $fillable = [
        'nama_role',
        'deskripsi'
    ];

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relasi many-to-many dengan users
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'id_role', 'id_user');
    }

    // Relasi many-to-many dengan permissions
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'id_role', 'id_permission');
    }

    // Check if role has permission
    public function hasPermission($permissionName)
    {
        return $this->permissions()->where('nama_permission', $permissionName)->exists();
    }

    // Assign permission to role
    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('nama_permission', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching($permission->id_permission);
        }
    }

    // Remove permission from role
    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('nama_permission', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id_permission);
        }
    }
}