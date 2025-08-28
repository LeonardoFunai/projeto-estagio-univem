<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Projeto; // Adicione esta linha

class AtualizarEtapaProjetosAprovados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Altere a descrição para ser mais clara
    protected $signature = 'app:atualizar-etapas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza a etapa para "Resultado" em todos os projetos com status "aprovado" que ainda estão na etapa "Proposta"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando a atualização de etapas...');

        // Lógica principal:
        // 1. Busca todos os projetos...
        $projetosParaAtualizar = Projeto::where('status', 'aprovado') // ...onde o status é 'aprovado'
                                         ->where('etapa', 'Proposta')  // ...e a etapa ainda é 'Proposta'.
                                         ->get();

        if ($projetosParaAtualizar->isEmpty()) {
            $this->info('Nenhum projeto para atualizar. Tudo certo!');
            return;
        }

        // 2. Percorre cada um e atualiza a etapa
        foreach ($projetosParaAtualizar as $projeto) {
            $projeto->etapa = 'Resultado';
            $projeto->save();
            $this->line('Projeto #' . $projeto->id . ' (' . $projeto->titulo . ') atualizado para a etapa Resultado.');
        }

        $this->info('Atualização concluída com sucesso!');
    }
}