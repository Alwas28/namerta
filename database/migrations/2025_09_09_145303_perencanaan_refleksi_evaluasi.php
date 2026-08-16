<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soal_perencanaan_refleksi_evaluasi', function (Blueprint $table) {
            $table->id('id_soal_perencanaan_refleksi_evaluasi');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->enum('jenis', ['ruang_kolaborasi', 'refleksi_terbimbing', 'demonstrasi_konseptual', 'elaborasi_konseptual']);
            $table->text('perencanaan')->nullable();
            $table->text('refleksi')->nullable();
            $table->text('evaluasi')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::create('jawaban_perencanaan_refleksi_evaluasi', function (Blueprint $table) {
            $table->id('id_jawaban_perencanaan_refleksi_evaluasi');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->enum('jenis', ['ruang_kolaborasi', 'refleksi_terbimbing', 'demonstrasi_konseptual', 'elaborasi_konseptual']);
            $table->text('perencanaan')->nullable();
            $table->integer('nilai_perencanaan')->nullable();
            $table->text('refleksi')->nullable();
            $table->integer('nilai_refleksi')->nullable();
            $table->text('evaluasi')->nullable();
            $table->integer('nilai_evaluasi')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soal_perencanaan_refleksi_evaluasi');
        Schema::dropIfExists('jawaban_perencanaan_refleksi_evaluasi');
    }
};
