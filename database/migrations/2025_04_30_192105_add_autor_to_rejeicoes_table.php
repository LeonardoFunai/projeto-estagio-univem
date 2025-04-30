<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rejeicoes', function (Blueprint $table) {
            $table->string('autor')->nullable()->after('motivo'); // Pode ajustar a posição se quiser
        });
    }
    
    public function down()
    {
        Schema::table('rejeicoes', function (Blueprint $table) {
            $table->dropColumn('autor');
        });
    }
    
};
