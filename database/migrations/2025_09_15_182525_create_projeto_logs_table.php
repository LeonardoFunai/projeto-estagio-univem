<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projeto_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->string('acao'); 
            $table->text('descricao');
            $table->json('dados_antigos')->nullable(); 
            $table->json('dados_novos')->nullable();   
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_logs');
    }
};