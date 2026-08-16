<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table modul
        Schema::create('modul', function (Blueprint $table) {
            $table->id('id_modul');
            $table->string('nama_modul');
            $table->longText('desk')->nullable();
            $table->longText('pengalaman_belajar')->nullable();
            $table->longText('koneksi_materi')->nullable();
            $table->longText('tes_kompetensi')->nullable();
            $table->longText('aksi_nyata')->nullable();
            $table->unsignedBigInteger('id_mata_pelajaran');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')->on('mata_pelajaran')->onDelete('cascade');
        });

        // Table materi
        Schema::create('materi', function (Blueprint $table) {
            $table->id('id_materi');
            $table->unsignedBigInteger('id_modul');
            $table->string('nama_materi');
            $table->longText('mulai_dari_diri')->nullable();
            $table->longText('eksplorasi_konsep')->nullable();
            $table->longText('ruang_kolaborasi')->nullable();
            $table->longText('refleksi_terbimbing')->nullable();
            $table->longText('demonstrasi_konseptual')->nullable();
            $table->longText('elaborasi_pemahaman')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
        });

        // Table soal_tes_kompetensi
        Schema::create('soal_tes_kompetensi', function (Blueprint $table) {
            $table->id('id_soal_tes_kompetensi');
            $table->unsignedBigInteger('id_modul');
            $table->string('soal');
            $table->string('a');
            $table->string('b');
            $table->string('c');
            $table->string('d');
            $table->enum('jawaban_benar', ['a', 'b', 'c', 'd']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
        });

        // Table checklist
        Schema::create('checklist', function (Blueprint $table) {
            $table->id('id_checklist');
            $table->unsignedBigInteger('id_user');
            $table->enum('aksi_nyata', ['Y', 'N'])->default('N');
            $table->enum('mulai_dari_diri', ['Y', 'N'])->default('N');
            $table->enum('eksplorasi_konsep', ['Y', 'N'])->default('N');
            $table->enum('ruang_kolaborasi', ['Y', 'N'])->default('N');
            $table->enum('refleksi_terbimbing', ['Y', 'N'])->default('N');
            $table->enum('demonstrasi_konseptual', ['Y', 'N'])->default('N');
            $table->enum('elaborasi_pemahaman', ['Y', 'N'])->default('N');
            $table->enum('uji_kompetensi', ['Y', 'N'])->default('N');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table eksplorasi_konsep
        Schema::create('eksplorasi_konsep', function (Blueprint $table) {
            $table->id('id_eksplorasi_konsep');
            $table->unsignedBigInteger('id_materi');
            $table->longText('konsep');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eksplorasi_konsep');
        Schema::dropIfExists('checklist');
        Schema::dropIfExists('soal_tes_kompetensi');
        Schema::dropIfExists('materi');
        Schema::dropIfExists('modul');
    }
};