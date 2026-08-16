<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table jawaban_mulai_dari_diri
        Schema::create('jawaban_mulai_dari_diri', function (Blueprint $table) {
            $table->id('id_jawaban_mulai_dari_diri');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table jawaban_ruang_kolaborasi
        Schema::create('jawaban_ruang_kolaborasi', function (Blueprint $table) {
            $table->id('id_jawaban_ruang_kolaborasi');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table jawaban_refleksi_terbimbing
        Schema::create('jawaban_refleksi_terbimbing', function (Blueprint $table) {
            $table->id('id_jawaban_refleksi_terbimbing');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table jawaban_elaborasi_pemahaman
        Schema::create('jawaban_elaborasi_pemahaman', function (Blueprint $table) {
            $table->id('id_jawaban_elaborasi_pemahaman');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table jawaban_demonstrasi_konseptual
        Schema::create('jawaban_demonstrasi_konseptual', function (Blueprint $table) {
            $table->id('id_demonstrasi_konseptual');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Table jawaban_tes_kompetensi
        Schema::create('jawaban_tes_kompetensi', function (Blueprint $table) {
            $table->id('id_jawaban_tes_kompetensi');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_soal_tes_kompetensi');
            $table->string('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_soal_tes_kompetensi')->references('id_soal_tes_kompetensi')->on('soal_tes_kompetensi')->onDelete('cascade');
        });

        // Table jawaban_aksi_nyata
        Schema::create('jawaban_aksi_nyata', function (Blueprint $table) {
            $table->id('id_jawaban_aksi_nyata');
            $table->unsignedBigInteger('id_modul');
            $table->unsignedBigInteger('id_user');
            $table->longText('jawaban');
            $table->enum('benar', ['Y', 'N'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jawaban_aksi_nyata');
        Schema::dropIfExists('jawaban_tes_kompetensi');
        Schema::dropIfExists('jawaban_demonstrasi_konseptual');
        Schema::dropIfExists('jawaban_elaborasi_pemahaman');
        Schema::dropIfExists('jawaban_refleksi_terbimbing');
        Schema::dropIfExists('jawaban_ruang_kolaborasi');
        Schema::dropIfExists('jawaban_mulai_dari_diri');
    }
};