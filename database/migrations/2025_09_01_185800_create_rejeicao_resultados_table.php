<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// No novo arquivo de migration de rejeicao_resultados

    public function up(): void
    {
        Schema::create('rejeicao_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultado_id')->constrained('resultados')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID de quem rejeitou
            $table->text('motivo'); // O parecer da rejeição
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rejeicao_resultados');
    }
};
