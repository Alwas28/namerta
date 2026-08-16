<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            // Gunakan integer biasa tanpa unsigned
            $table->integer('id_user');
            $table->integer('id_role');
            $table->timestamp('created_at')->useCurrent();
            
            // Primary key composite
            $table->primary(['id_user', 'id_role']);
            
            // Indexes saja, tanpa foreign key dulu
            $table->index('id_user');
            $table->index('id_role');
        });

        // Migrate existing users
        $this->assignRolesToExistingUsers();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }

    private function assignRolesToExistingUsers(): void
    {
        // Get role IDs
        $roles = DB::table('roles')->pluck('id_role', 'nama_role');
        
        if ($roles->isEmpty()) {
            return;
        }
        
        // Get all users
        $users = DB::table('users')
            ->leftJoin('profile', 'users.id_user', '=', 'profile.id_user')
            ->select('users.id_user', 'users.is_admin', 'profile.id_status')
            ->get();
        
        foreach ($users as $user) {
            $roleId = null;
            
            if ($user->is_admin === 'Y') {
                $roleId = $roles['super_admin'] ?? null;
            } else {
                $roleId = $roles[$user->id_status] ?? $roles['siswa'] ?? null;
            }
            
            if ($roleId && $user->id_user) {
                DB::table('user_roles')->insertOrIgnore([
                    'id_user' => $user->id_user,
                    'id_role' => $roleId
                ]);
            }
        }
    }
};