<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            // Verifica se as colunas existem antes de tentar removê-las
            if (Schema::hasColumn('projetos', 'professor_id')) {
                $table->dropForeign(['professor_id']);
                $table->dropColumn('professor_id');
            }
            if (Schema::hasColumn('projetos', 'aluno_id')) {
                $table->dropForeign(['aluno_id']);
                $table->dropColumn('aluno_id');
            }
        });
    }

    public function down(): void
    {
        // Método para reverter, caso precise
        Schema::table('projetos', function (Blueprint $table) {
            $table->foreignId('aluno_id')->nullable()->constrained('alunos');
            $table->foreignId('professor_id')->nullable()->constrained('professores');
        });
    }
};