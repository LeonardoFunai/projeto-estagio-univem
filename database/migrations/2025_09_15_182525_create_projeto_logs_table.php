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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Quem realizou a ação
            $table->string('acao'); // Ex: 'PROPOSTA_CRIADA', 'ETAPA_ALTERADA', 'STATUS_ALTERADO'
            $table->text('descricao'); // Ex: "Status alterado de 'Editando' para 'Entregue'"
            $table->json('dados_antigos')->nullable(); // Opcional: Para guardar o estado anterior
            $table->json('dados_novos')->nullable();   // Opcional: Para guardar o novo estado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_logs');
    }
};