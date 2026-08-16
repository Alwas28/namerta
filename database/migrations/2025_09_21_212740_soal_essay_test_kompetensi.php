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
        Schema::create('soal_essay_tes_kompetensi', function (Blueprint $table) {
            $table->id('id_soal_essay');
            $table->unsignedBigInteger('id_modul');
            $table->string('soal');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
        });

        Schema::create('jawaban_essay_tes_kompetensi', function (Blueprint $table) {
            $table->id('id_jawaban_essay');
            $table->unsignedBigInteger('id_soal_essay');
            $table->unsignedBigInteger('id_user');
            $table->text('jawaban');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_soal_essay')->references('id_soal_essay')->on('soal_essay_tes_kompetensi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::create('jenis_tes_kompetensi', function (Blueprint $table) {
            $table->id('id_jenis_tes_kompetensi');
            $table->unsignedBigInteger('id_modul');
            $table->enum('essay', ['Y', 'N'])->default('N');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soal_essay_tes_kompetensi');
        Schema::dropIfExists('jawaban_essay_tes_kompetensi');
        Schema::dropIfExists('jenis_tes_kompetensi');
    }
};
