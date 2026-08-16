<?php

// Updated Migration - database/migrations/create_ai_chat_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiChatMessagesTable2 extends Migration
{
    public function up()
    {
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users', 'id_user')->onDelete('cascade');
            $table->unsignedBigInteger('id_modul');
            $table->unsignedBigInteger('id_materi');
            $table->text('user_message');
            $table->text('ai_response');
            $table->string('context')->default('eksplorasi_konsep');
            $table->json('metadata')->nullable(); // For storing additional context
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_modul')->references('id_modul')->on('modul')->onDelete('cascade');
            $table->foreign('id_materi')->references('id_materi')->on('materi')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['id_user', 'id_modul', 'id_materi']);
            $table->index(['context', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_chat_messages');
    }
}