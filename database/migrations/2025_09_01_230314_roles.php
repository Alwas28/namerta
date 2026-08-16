<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->integer('id_role')->autoIncrement();
            $table->string('nama_role', 50);
            $table->string('deskripsi', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Insert default roles
        DB::table('roles')->insert([
            ['nama_role' => 'super_admin', 'deskripsi' => 'Akses penuh sistem'],
            ['nama_role' => 'admin_sekolah', 'deskripsi' => 'Admin tingkat sekolah'],
            ['nama_role' => 'guru', 'deskripsi' => 'Akses guru'],
            ['nama_role' => 'siswa', 'deskripsi' => 'Akses siswa'],
            ['nama_role' => 'tendik', 'deskripsi' => 'Tenaga kependidikan']
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};