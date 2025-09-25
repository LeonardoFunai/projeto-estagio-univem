<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf')->unique()->nullable()->after('email');
            $table->string('ra')->unique()->nullable()->after('cpf');
            $table->date('data_nascimento')->nullable()->after('ra');
            $table->foreignId('curso_id')->nullable()->after('data_nascimento')->constrained('cursos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropColumn(['cpf', 'ra', 'data_nascimento', 'curso_id']);
        });
    }
};