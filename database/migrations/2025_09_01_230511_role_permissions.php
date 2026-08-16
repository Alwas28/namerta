<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->integer('id_role');
            $table->integer('id_permission');
            $table->timestamp('created_at')->useCurrent();
            
            // Primary key composite
            $table->primary(['id_role', 'id_permission']);
            
            // Foreign keys
            $table->foreign('id_role')->references('id_role')->on('roles')->onDelete('cascade');
            $table->foreign('id_permission')->references('id_permission')->on('permissions')->onDelete('cascade');
        });

        // Assign permissions to roles
        $this->assignDefaultPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }

    private function assignDefaultPermissions(): void
    {
        // Get all permissions for super admin
        $permissions = DB::table('permissions')->pluck('id_permission');
        $superAdminId = DB::table('roles')->where('nama_role', 'super_admin')->value('id_role');
        
        // Super admin gets all permissions
        foreach ($permissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'id_role' => $superAdminId,
                'id_permission' => $permissionId
            ]);
        }

        // Admin sekolah permissions
        $adminSekolahId = DB::table('roles')->where('nama_role', 'admin_sekolah')->value('id_role');
        $adminPermissions = DB::table('permissions')
            ->whereIn('nama_permission', [
                'user.view', 'user.create', 'user.edit',
                'kelas.view', 'kelas.create', 'kelas.edit', 'kelas.delete',
                'mapel.view', 'mapel.create', 'mapel.edit', 'mapel.delete',
                'modul.view', 'soal.view',
                'jawaban.view_all', 'report.view_all', 'report.export'
            ])
            ->pluck('id_permission');
            
        foreach ($adminPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'id_role' => $adminSekolahId,
                'id_permission' => $permissionId
            ]);
        }

        // Guru permissions
        $guruId = DB::table('roles')->where('nama_role', 'guru')->value('id_role');
        $guruPermissions = DB::table('permissions')
            ->whereIn('nama_permission', [
                'kelas.view', 'mapel.view',
                'modul.view', 'modul.create', 'modul.edit', 'modul.delete',
                'soal.view', 'soal.create', 'soal.edit', 'soal.delete',
                'jawaban.view_all', 'jawaban.grade',
                'report.view_class'
            ])
            ->pluck('id_permission');
            
        foreach ($guruPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'id_role' => $guruId,
                'id_permission' => $permissionId
            ]);
        }

        // Siswa permissions
        $siswaId = DB::table('roles')->where('nama_role', 'siswa')->value('id_role');
        $siswaPermissions = DB::table('permissions')
            ->whereIn('nama_permission', [
                'kelas.view', 'mapel.view', 'modul.view', 'soal.view',
                'jawaban.view_own', 'jawaban.create',
                'report.view_own'
            ])
            ->pluck('id_permission');
            
        foreach ($siswaPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'id_role' => $siswaId,
                'id_permission' => $permissionId
            ]);
        }

        // Tendik permissions
        $tendikId = DB::table('roles')->where('nama_role', 'tendik')->value('id_role');
        $tendikPermissions = DB::table('permissions')
            ->whereIn('nama_permission', [
                'user.view',
                'kelas.view', 'mapel.view',
                'report.view_all'
            ])
            ->pluck('id_permission');
            
        foreach ($tendikPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'id_role' => $tendikId,
                'id_permission' => $permissionId
            ]);
        }
    }
};