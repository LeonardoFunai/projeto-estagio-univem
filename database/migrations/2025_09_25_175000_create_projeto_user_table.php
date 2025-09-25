<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela-pivô que liga usuários (alunos e professores) aos projetos
        Schema::create('projeto_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Garante que um usuário não pode ser adicionado duas vezes no mesmo projeto
            $table->unique(['projeto_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_user');
    }
};