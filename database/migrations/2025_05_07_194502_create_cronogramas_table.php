<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->string('atividade');
            $table->string('mes');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cronogramas');
    }
};
