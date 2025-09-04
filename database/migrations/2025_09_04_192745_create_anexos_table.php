<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anexos', function (Blueprint $table) {
            $table->id();
            // Cria a ligação com a tabela de resultados. Se um resultado for apagado, os anexos somem junto.
            $table->foreignId('resultado_id')->constrained()->onDelete('cascade');
            $table->string('nome_original'); // Nome que o arquivo tinha no computador do usuário
            $table->string('path');           // Caminho onde salvamos o arquivo no servidor
            $table->string('mime_type')->nullable(); // Tipo do arquivo (imagem, pdf, etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anexos');
    }
};