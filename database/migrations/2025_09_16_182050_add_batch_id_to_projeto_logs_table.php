<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projeto_logs', function (Blueprint $table) {
            // Adiciona um ID para agrupar logs de uma mesma requisição.
            // Usamos um UUID para garantir que seja único.
            $table->uuid('batch_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('projeto_logs', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};