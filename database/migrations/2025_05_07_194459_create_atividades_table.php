<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->text('o_que_fazer');
            $table->text('como_fazer');
            $table->integer('carga_horaria');
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('atividades');
    }
};
