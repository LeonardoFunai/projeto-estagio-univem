<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\PdfsEmLoteProntos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class GerarPdfsEmLoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tenta executar o job por 10 minutos antes de falhar.
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Collection $projetos, 
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Garante que o diretório de zips exista
        Storage::disk('public')->makeDirectory('lotes-pdf');

        $zip = new ZipArchive;
        $zipFileName = 'Lote_Projetos_' . $this->user->id . '_' . time() . '.zip';
        $zipPath = Storage::disk('public')->path('lotes-pdf/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            // Falha ao criar o ZIP, podemos notificar o usuário sobre o erro
            return;
        }

        foreach ($this->projetos as $projeto) {
            
            try {
                // ==========================================================
                // 1. GERAR PDF DA PROPOSTA (SEMPRE)
                // Se o projeto existe, a proposta deve ser incluída no ZIP.
                // ==========================================================
                $professores = $projeto->professores;
                $alunos = $projeto->alunos;
                $pdfProposta = Pdf::loadView('projetos.pdf', compact('projeto', 'professores', 'alunos'));
                
                $fileNameProposta = 'Proposta_' . $projeto->id . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $projeto->titulo) . '.pdf';
                
                $zip->addFromString($fileNameProposta, $pdfProposta->output());


                // ==========================================================
                // 2. GERAR PDF DO RELATÓRIO (SOMENTE SE ENTREGUE E NA ETAPA CORRETA)
                // O Relatório só deve ser gerado se o Resultado existir E NÃO ESTIVER em rascunho ('editando').
                // Este é o ponto de correção.
                // ==========================================================
                if ($projeto->etapa === 'Resultado' && $projeto->resultado) {
                    $resultado = $projeto->resultado;

                    // VERIFICAÇÃO DE ENTREGA: Assume que 'editando' é o status de rascunho (não entregue)
                    // Se o status for diferente de 'editando', consideramos que o aluno submeteu.
                    // Você deve ajustar 'editando' se o valor do banco for outro para rascunho.
                    if ($resultado->status !== 'editando') {
                        
                        $pdfRelatorio = Pdf::loadView('pdf.resultados-relatorio', compact('resultado'));
                        
                        $fileNameRelatorio = 'Relatorio_' . $projeto->id . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $projeto->titulo) . '.pdf';
                        
                        $zip->addFromString($fileNameRelatorio, $pdfRelatorio->output());
                    }
                }

            } catch (\Exception $e) {
                // Se um PDF falhar, adiciona um arquivo de log de erro ao zip
                $zip->addFromString('ERRO_NO_PROJETO_' . $projeto->id . '.txt', 'Houve um erro ao gerar o PDF para este projeto: ' . $e->getMessage());
            }
        }

        $zip->close();

        // 6. Notificar o usuário com o link de download
        $downloadUrl = Storage::disk('public')->url('lotes-pdf/' . $zipFileName);
        $this->user->notify(new PdfsEmLoteProntos($downloadUrl, $this->projetos->count()));
    }
}