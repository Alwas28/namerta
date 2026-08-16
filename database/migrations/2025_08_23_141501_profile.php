<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table users
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('username');
            $table->string('password');
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->enum('is_admin', ['Y', 'N'])->default('N');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Table profile
        Schema::create('profile', function (Blueprint $table) {
            $table->id('id_profile');
            $table->string('nama');
            $table->string('nip_nis');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_sekolah')->nullable();
            $table->string('alamat')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('agama')->nullable();
            $table->enum('status', ['siswa', 'guru', 'tendik'])->nullable();
            $table->string('id_status')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table sekolah
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id('id_sekolah');
            $table->string('kode_sekolah');
            $table->string('nama_sekolah');
            $table->string('alamat')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('profile')->nullable();
            $table->string('logo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Table mata_pelajaran
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id('id_mata_pelajaran');
            $table->string('nama_mata_pelajaran');
            $table->text('deskripsi')->nullable();
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Table kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('id_kelas');
            $table->string('nama_kelas');
            $table->text('deskripsi')->nullable();
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Table kelas_mp (junction table)
        Schema::create('kelas_mp', function (Blueprint $table) {
            $table->id('id_kelas_mp');
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_mata_pelajaran');
            $table->unsignedBigInteger('id_user');
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Add foreign key for profile table after sekolah is created
        Schema::table('profile', function (Blueprint $table) {
            $table->foreign('id_sekolah')->references('id_sekolah')->on('sekolah')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kelas_mp');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('profile');
        Schema::dropIfExists('sekolah');
        Schema::dropIfExists('users');
    }
};