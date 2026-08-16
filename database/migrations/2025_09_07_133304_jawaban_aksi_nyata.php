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
        Schema::create('jawaban_koneksi_materi', function (Blueprint $table) {
            $table->id('id_jawaban_koneksi_materi');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jawaban_koneksi_materi');
    }
};
