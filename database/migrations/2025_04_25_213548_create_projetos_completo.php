<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // PROJETOS
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('periodo');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('publico_alvo')->nullable();
            $table->text('introducao')->nullable();
            $table->text('objetivo_geral')->nullable();
            $table->text('justificativa')->nullable();
            $table->text('metodologia')->nullable();
            $table->text('recursos')->nullable();
            $table->text('resultados_esperados')->nullable();
            $table->string('numero_projeto')->nullable();
            $table->date('data_recebimento_napex')->nullable();
            $table->date('data_encaminhamento_parecer')->nullable();
            $table->enum('aprovado_napex', ['sim', 'nao'])->nullable();
            $table->text('motivo_napex')->nullable();
            $table->enum('aprovado_coordenador', ['sim', 'nao'])->nullable();
            $table->text('motivo_coordenador')->nullable();
            $table->date('data_parecer_coordenador')->nullable();
            $table->string('arquivo')->nullable();
            $table->enum('status', ['editando', 'entregue'])->default('editando');
            $table->timestamps();
        });

        // ALUNOS
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->string('nome');
            $table->string('ra');
            $table->string('curso');
            $table->timestamps();
        });

        // PROFESSORES
        Schema::create('professores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('area')->nullable();
            $table->timestamps();
        });

        // ATIVIDADES
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->text('o_que_fazer');
            $table->text('como_fazer');
            $table->integer('carga_horaria');
            $table->timestamps();
        });

        // CRONOGRAMAS
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->string('atividade');
            $table->string('mes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronogramas');
        Schema::dropIfExists('atividades');
        Schema::dropIfExists('professores');
        Schema::dropIfExists('alunos');
        Schema::dropIfExists('projetos');
    }
};
