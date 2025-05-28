<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Supondo que o nome do arquivo seja algo como:
// 2025_05_07_193605_create_projetos_table.php
// (ou seja, roda ANTES de create_professores_table.php)

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('periodo');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->text('publico_alvo')->nullable();
            $table->text('introducao')->nullable();
            $table->text('objetivo_geral')->nullable();
            $table->text('justificativa')->nullable();
            $table->text('metodologia')->nullable();
            $table->text('recursos')->nullable();
            $table->text('resultados_esperados')->nullable();
            $table->string('numero_projeto')->nullable();
            $table->date('data_entrega')->nullable();
            $table->date('data_parecer_napex')->nullable();
            $table->enum('aprovado_napex', ['pendente', 'sim', 'nao'])->default('pendente');
            $table->text('motivo_napex')->nullable();
            $table->enum('aprovado_coordenador', ['pendente', 'sim', 'nao'])->default('pendente');
            $table->text('motivo_coordenador')->nullable();
            $table->date('data_parecer_coordenador')->nullable();
            $table->string('status')->default('editando');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // FK para users, OK

            // MODIFICADO: Coluna professor_id definida, mas a CONSTRAINT de chave estrangeira
            // para 'professores' NÃO é criada aqui. Ela será criada em uma migração posterior.
            $table->foreignId('professor_id')->nullable();
            // Alternativamente, se preferir ser mais explícito sobre o tipo sem usar foreignId para esta coluna temporariamente:
            // $table->unsignedBigInteger('professor_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('projetos');
    }
};