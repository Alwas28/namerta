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
    public function up(): void
    {
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->integer('id_ta')->autoIncrement();
            $table->string('nama_ta');
            $table->enum('aktif', ['Y', 'N'])->default('N');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_pelajaran');
    }
};
