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
        Schema::table('anexos', function (Blueprint $table) {
            // Altera a coluna 'descricao' para VARCHAR com 1000 caracteres
            $table->string('descricao', 1000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anexos', function (Blueprint $table) {
            // Reverte a coluna para o estado anterior (VARCHAR 255)
            $table->string('descricao', 255)->change();
        });
    }
};