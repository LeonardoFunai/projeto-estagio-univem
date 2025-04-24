<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('editando');
            $table->string('titulo');
            $table->string('periodo');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('publico_alvo')->nullable();
            $table->text('introducao')->nullable();
            $table->text('objetivo_geral')->nullable();
            $table->text('justificativa')->nullable();
            $table->text('metodologia')->nullable();
            $table->text('execucao_projeto')->nullable();
            $table->text('documentacao_execucao')->nullable();
            $table->text('relatorio_final')->nullable();
            $table->text('cronograma')->nullable();
            $table->text('recursos')->nullable();
            $table->text('resultados_esperados')->nullable();
            $table->string('arquivo')->nullable();
            $table->timestamps();
        });

        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('ra');
            $table->string('curso');
            $table->timestamps();
        });

        Schema::create('professores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('area')->nullable();
            $table->timestamps();
        });

        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->onDelete('cascade');
            $table->text('o_que_fazer');
            $table->text('como_fazer');
            $table->integer('carga_horaria');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atividades');
        Schema::dropIfExists('professores');
        Schema::dropIfExists('alunos');
        Schema::dropIfExists('projetos');
    }
};
