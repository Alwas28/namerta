<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            
            // Use same data type as your existing tables
            // Change these based on your actual table structure:
            
            // If kelas_ta.id_kelas_ta is INT, use unsignedInteger
            // If kelas_ta.id_kelas_ta is BIGINT, use unsignedBigInteger
            $table->unsignedInteger('id_kelas_ta'); // Change to unsignedBigInteger if needed
            
            // If users.id_user is BIGINT, use unsignedBigInteger  
            // If users.id_user is INT, use unsignedInteger
            $table->unsignedBigInteger('id_user'); // Change to unsignedInteger if needed
            
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->timestamps();
            
            // Add indexes
            $table->index('id_kelas_ta');
            $table->index('id_user');
            $table->index('aktif');
            
            // Unique constraint
            $table->unique(['id_kelas_ta', 'id_user'], 'unique_siswa_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa_kelas');
    }
};