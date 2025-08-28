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
        Schema::table('projetos', function (Blueprint $table) {
            // Adiciona a nova coluna 'etapa' depois da coluna 'status'
            // O valor padrão 'Proposta' garante que todos os seus projetos existentes comecem na etapa correta.
            $table->string('etapa')->default('Proposta')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            // Isso permite que você desfaça a migração se necessário
            $table->dropColumn('etapa');
        });
    }
};
