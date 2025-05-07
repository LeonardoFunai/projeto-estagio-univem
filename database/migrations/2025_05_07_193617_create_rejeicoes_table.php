<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rejeicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->text('motivo');
            $table->date('data_rejeicao')->nullable();
            $table->string('autor'); // plain string (não foreignId) para bater com o model
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rejeicoes');
    }
};
