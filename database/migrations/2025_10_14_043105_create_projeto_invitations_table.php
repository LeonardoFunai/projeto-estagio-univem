<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projeto_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem convidou
            $table->string('email'); // Email de quem foi convidado
            $table->enum('role', ['aluno', 'professor']); // O papel do convidado no projeto
            $table->string('token')->unique();
            $table->enum('status', ['pendente', 'aceito', 'recusado'])->default('pendente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_invitations');
    }
};
