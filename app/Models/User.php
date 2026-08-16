<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    
    protected $fillable = [
        'username',
        'password',
        'aktif',
        'is_admin'
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relasi dengan profile
    public function profile()
    {
        return $this->hasOne(Profile::class, 'id_user', 'id_user');
    }
    

    // Relasi many-to-many dengan roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'id_user', 'id_role');
    }

    // Get user permissions through roles
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            'user_roles',
            'id_user', // Foreign key on user_roles table
            'id_permission', // Foreign key on permissions table
            'id_user', // Local key on users table
            'id_role' // Local key on user_roles table
        );
    }

    // Check if user has specific role
    public function hasRole($roleName)
    {
        return $this->roles()->where('nama_role', $roleName)->exists();
    }

    // Check if user has any of the given roles
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return $this->roles()->whereIn('nama_role', $roles)->exists();
    }

    // Check if user has permission
    public function hasPermission($permissionName)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('nama_permission', $permissionName)->exists()) {
                return true;
            }
        }
        return false;
    }

    // Get primary role (first role)
    public function getPrimaryRole()
    {
        return $this->roles()->first();
    }

    // Backward compatibility with is_admin
    public function isAdmin()
    {
        return $this->is_admin === 'Y' || $this->hasRole('super_admin');
    }

    
}