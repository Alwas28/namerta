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
        Schema::create('checklist_materi', function (Blueprint $table) {
            $table->integer('id_checklist_materi')->autoIncrement();
            $table->string('id_user');
            $table->string('id_materi');
            $table->enum('mulai_dari_diri', ['Y', 'N'])->default('N');
            $table->enum('eksplorasi_konsep', ['Y', 'N'])->default('N');
            $table->enum('ruang_kolaborasi', ['Y', 'N'])->default('N');
            $table->enum('refleksi_terbimbing', ['Y', 'N'])->default('N');
            $table->enum('demonstrasi_konseptual', ['Y', 'N'])->default('N');
            $table->enum('elaborasi_pemahaman', ['Y', 'N'])->default('N');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_materi');
    }
};
