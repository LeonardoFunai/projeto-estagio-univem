<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    

    public function up(): void
    {
        Schema::table('resultados', function (Blueprint $table) {
            $table->string('aprovado_napex')->default('pendente')->after('status');
            $table->text('parecer_napex')->nullable()->after('aprovado_napex');
            $table->string('aprovado_coordenador')->default('pendente')->after('parecer_napex');
            $table->text('parecer_coordenador')->nullable()->after('aprovado_coordenador');
        });
    }

    public function down(): void
    {
        Schema::table('resultados', function (Blueprint $table) {
            $table->dropColumn(['aprovado_napex', 'parecer_napex', 'aprovado_coordenador', 'parecer_coordenador']);
        });
    }
};
