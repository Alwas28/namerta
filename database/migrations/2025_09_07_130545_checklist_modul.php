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
        Schema::create('checklist_modul', function (Blueprint $table) {
            $table->integer('id_checklist_modul')->autoIncrement();
            $table->string('id_user');
            $table->string('id_modul');
            $table->enum('pengalaman_belajar', ['Y', 'N'])->default('N');
            $table->enum('materi', ['Y', 'N'])->default('N');
            $table->enum('koneksi_materi', ['Y', 'N'])->default('N');
            $table->enum('aksi_nyata', ['Y', 'N'])->default('N');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_modul');
    }
};
