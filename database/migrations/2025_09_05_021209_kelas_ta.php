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
        Schema::create('kelas_ta', function (Blueprint $table) {
            $table->integer('id_kelas_ta')->autoIncrement();
            $table->string('id_kelas');
            $table->string('id_ta');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_ta');
    }
};
