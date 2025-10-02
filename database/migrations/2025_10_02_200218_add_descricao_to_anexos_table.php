<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anexos', function (Blueprint $table) {
            // Adiciona a coluna 'descricao' após a coluna 'resultado_id'
            $table->string('descricao')->after('resultado_id');
        });
    }

    public function down(): void
    {
        Schema::table('anexos', function (Blueprint $table) {
            // Remove a coluna caso a migration seja revertida
            $table->dropColumn('descricao');
        });
    }
};