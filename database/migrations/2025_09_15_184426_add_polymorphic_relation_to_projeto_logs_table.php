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
        Schema::table('projeto_logs', function (Blueprint $table) {
            // Adiciona as colunas para o relacionamento polimórfico
            // loggable_type guardará o nome do Model (ex: App\Models\Projeto)
            // loggable_id guardará o ID do registro (ex: o ID do projeto ou do resultado)
            $table->morphs('loggable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projeto_logs', function (Blueprint $table) {
            $table->dropMorphs('loggable');
        });
    }
};