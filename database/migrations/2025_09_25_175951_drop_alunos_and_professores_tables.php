<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove a tabela 'professores' se ela existir
        Schema::dropIfExists('professores');
        
        // Remove a tabela 'alunos' se ela existir
        Schema::dropIfExists('alunos');
    }

    public function down(): void
    {

        Schema::create('professores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();
        });

        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();
        });
    }
};