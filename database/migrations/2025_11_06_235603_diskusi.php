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
        // Tabel untuk topik diskusi
        Schema::create('topik_diskusi', function (Blueprint $table) {
            $table->id('id_topik_diskusi');
            $table->unsignedBigInteger('id_materi');
            $table->unsignedBigInteger('id_user'); // guru yang membuat
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('status', ['aktif', 'ditutup'])->default('aktif');
            $table->timestamp('dibuka_pada')->nullable();
            $table->timestamp('ditutup_pada')->nullable();
            $table->timestamps();

            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // Tabel untuk komentar diskusi
        Schema::create('komentar_diskusi', function (Blueprint $table) {
            $table->id('id_komentar');
            $table->unsignedBigInteger('id_topik_diskusi');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_parent')->nullable(); // untuk reply
            $table->text('konten');
            $table->boolean('diedit')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_topik_diskusi')->references('id_topik_diskusi')->on('topik_diskusi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_parent')->references('id_komentar')->on('komentar_diskusi')->onDelete('cascade');
        });

        // Tabel untuk like komentar
        Schema::create('like_komentar', function (Blueprint $table) {
            $table->id('id_like');
            $table->unsignedBigInteger('id_komentar');
            $table->unsignedBigInteger('id_user');
            $table->timestamps();

            $table->foreign('id_komentar')->references('id_komentar')->on('komentar_diskusi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            
            $table->unique(['id_komentar', 'id_user']);
        });

        // Tabel untuk tracking pembacaan
        Schema::create('diskusi_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_topik_diskusi');
            $table->unsignedBigInteger('id_user');
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->foreign('id_topik_diskusi')->references('id_topik_diskusi')->on('topik_diskusi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            
            $table->unique(['id_topik_diskusi', 'id_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskusi_read_status');
        Schema::dropIfExists('like_komentar');
        Schema::dropIfExists('komentar_diskusi');
        Schema::dropIfExists('topik_diskusi');
    }
};