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
        Schema::table('cronogramas', function (Blueprint $table) {
            if (Schema::hasColumn('cronogramas', 'mes')) {
                $table->dropColumn('mes');
            }
            $table->string('mes_inicio', 20)->after('atividade'); // Ou o tipo de dado que preferir para mês
            $table->string('mes_fim', 20)->after('mes_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropColumn('mes_inicio');
            $table->dropColumn('mes_fim');
            $table->string('mes', 20)->after('atividade'); // Para reverter à estrutura antiga
        });
    }
};
