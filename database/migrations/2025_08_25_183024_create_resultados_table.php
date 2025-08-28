<?php
// Dentro do arquivo da migration que você acabou de criar

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->unique()->constrained()->onDelete('cascade');

            // Seção: Relatório Principal (campos do PDF)
            $table->text('atividades_desenvolvidas');
            $table->text('comunidade_externa')->nullable();

            // Seção: Parcerias (campos do PDF)
            $table->string('parceiro_organizacao')->nullable();
            $table->string('parceiro_endereco')->nullable();
            $table->string('parceiro_cnpj')->nullable();
            $table->string('parceiro_responsavel')->nullable();
            $table->string('parceiro_tipo_participacao')->nullable();

            // Seção: Anexos (campos do PDF)
            $table->text('anexos_descricao')->nullable();
            $table->json('fotos_paths')->nullable()->comment('Armazena um array de caminhos para as fotos');
            $table->text('links_videos')->nullable();

            // Status do relatório
            $table->enum('status', ['rascunho', 'enviado', 'aprovado', 'reprovado'])->default('rascunho');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};
