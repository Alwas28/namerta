<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id_permission';
    
    protected $fillable = [
        'nama_permission',
        'deskripsi',
        'grup'
    ];

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relasi many-to-many dengan roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'id_permission', 'id_role');
    }

    // Get all users with this permission
    public function users()
    {
        $userIds = [];
        foreach ($this->roles as $role) {
            $userIds = array_merge($userIds, $role->users->pluck('id_user')->toArray());
        }
        return User::whereIn('id_user', array_unique($userIds))->get();
    }
}