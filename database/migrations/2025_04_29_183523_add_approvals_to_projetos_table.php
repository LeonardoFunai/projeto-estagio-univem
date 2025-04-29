<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->boolean('napex_aprovado')->default(false);
            $table->boolean('coordenacao_aprovado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropColumn(['napex_aprovado', 'coordenacao_aprovado']);
        });
    }
};

