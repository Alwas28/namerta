<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->integer('id_permission')->autoIncrement();
            $table->string('nama_permission', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->string('grup', 50)->nullable(); // Grouping: user_management, academic, content, etc
            $table->timestamp('created_at')->useCurrent();
        });

        // Insert permissions dengan format CRUD
        DB::table('permissions')->insert([
            // User Management
            ['nama_permission' => 'user.view', 'deskripsi' => 'Lihat daftar user', 'grup' => 'user_management'],
            ['nama_permission' => 'user.create', 'deskripsi' => 'Tambah user baru', 'grup' => 'user_management'],
            ['nama_permission' => 'user.edit', 'deskripsi' => 'Edit user', 'grup' => 'user_management'],
            ['nama_permission' => 'user.delete', 'deskripsi' => 'Hapus user', 'grup' => 'user_management'],
            
            // Kelas Management
            ['nama_permission' => 'kelas.view', 'deskripsi' => 'Lihat daftar kelas', 'grup' => 'academic'],
            ['nama_permission' => 'kelas.create', 'deskripsi' => 'Tambah kelas', 'grup' => 'academic'],
            ['nama_permission' => 'kelas.edit', 'deskripsi' => 'Edit kelas', 'grup' => 'academic'],
            ['nama_permission' => 'kelas.delete', 'deskripsi' => 'Hapus kelas', 'grup' => 'academic'],
            
            // Mata Pelajaran
            ['nama_permission' => 'mapel.view', 'deskripsi' => 'Lihat mata pelajaran', 'grup' => 'academic'],
            ['nama_permission' => 'mapel.create', 'deskripsi' => 'Tambah mata pelajaran', 'grup' => 'academic'],
            ['nama_permission' => 'mapel.edit', 'deskripsi' => 'Edit mata pelajaran', 'grup' => 'academic'],
            ['nama_permission' => 'mapel.delete', 'deskripsi' => 'Hapus mata pelajaran', 'grup' => 'academic'],
            
            // Modul & Materi
            ['nama_permission' => 'modul.view', 'deskripsi' => 'Lihat modul', 'grup' => 'content'],
            ['nama_permission' => 'modul.create', 'deskripsi' => 'Buat modul', 'grup' => 'content'],
            ['nama_permission' => 'modul.edit', 'deskripsi' => 'Edit modul', 'grup' => 'content'],
            ['nama_permission' => 'modul.delete', 'deskripsi' => 'Hapus modul', 'grup' => 'content'],
            
            // Soal & Tes
            ['nama_permission' => 'soal.view', 'deskripsi' => 'Lihat soal', 'grup' => 'assessment'],
            ['nama_permission' => 'soal.create', 'deskripsi' => 'Buat soal', 'grup' => 'assessment'],
            ['nama_permission' => 'soal.edit', 'deskripsi' => 'Edit soal', 'grup' => 'assessment'],
            ['nama_permission' => 'soal.delete', 'deskripsi' => 'Hapus soal', 'grup' => 'assessment'],
            
            // Jawaban & Nilai
            ['nama_permission' => 'jawaban.view_all', 'deskripsi' => 'Lihat semua jawaban', 'grup' => 'assessment'],
            ['nama_permission' => 'jawaban.view_own', 'deskripsi' => 'Lihat jawaban sendiri', 'grup' => 'assessment'],
            ['nama_permission' => 'jawaban.create', 'deskripsi' => 'Buat jawaban', 'grup' => 'assessment'],
            ['nama_permission' => 'jawaban.grade', 'deskripsi' => 'Beri nilai', 'grup' => 'assessment'],
            
            // Reports
            ['nama_permission' => 'report.view_all', 'deskripsi' => 'Lihat semua laporan', 'grup' => 'report'],
            ['nama_permission' => 'report.view_class', 'deskripsi' => 'Lihat laporan kelas', 'grup' => 'report'],
            ['nama_permission' => 'report.view_own', 'deskripsi' => 'Lihat laporan sendiri', 'grup' => 'report'],
            ['nama_permission' => 'report.export', 'deskripsi' => 'Export laporan', 'grup' => 'report']
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};