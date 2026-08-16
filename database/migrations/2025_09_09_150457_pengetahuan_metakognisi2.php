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
        Schema::create('soal_pengetahuan_metakognisi', function (Blueprint $table) {
            $table->id('id_soal_pengetahuan_metakognisi');
            $table->unsignedBigInteger('id_eksplorasi_konsep');
            $table->unsignedBigInteger('id_user');
            $table->string('deklaratif')->nullable();
            $table->string('prosedural')->nullable();
            $table->string('kondisional')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_eksplorasi_konsep')->references('id_eksplorasi_konsep')->on('eksplorasi_konsep')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::create('jawaban_pengetahuan_metakognisi', function (Blueprint $table) {
            $table->id('id_jawaban_pengetahuan_metakognisi');
            $table->unsignedBigInteger('id_eksplorasi_konsep');
            $table->unsignedBigInteger('id_user');
            $table->string('deklaratif')->nullable();
            $table->integer('nilai_deklaratif')->nullable();
            $table->string('prosedural')->nullable();
            $table->integer('nilai_prosedural')->nullable();
            $table->string('kondisional')->nullable();
            $table->integer('nilai_kondisional')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_eksplorasi_konsep')->references('id_eksplorasi_konsep')->on('eksplorasi_konsep')->onDelete('cascade');
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
        Schema::dropIfExists('soal_pengetahuan_metakognisi');
        Schema::dropIfExists('jawaban_pengetahuan_metakognisi');
    }
};
