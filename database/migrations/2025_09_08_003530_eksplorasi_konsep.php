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
        Schema::create('eksplorasi_konsep', function (Blueprint $table) {
            $table->id('id_eksplorasi_konsep');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user');
            $table->longText('isi_materi');
            $table->Text('pdf')->nullable();
            $table->Text('video')->nullable();
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
        Schema::dropIfExists('eksplorasi_konsep');
    }
};
