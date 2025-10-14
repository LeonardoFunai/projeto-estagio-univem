<?php

namespace App\Traits;

use App\Models\ProjetoLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogaAlteracoes
{
    private static ?string $logBatchId = null;

    protected static function getLogBatchId(): string
    {
        if (static::$logBatchId === null) {
            static::$logBatchId = (string) Str::uuid();
        }
        return static::$logBatchId;
    }

    protected static function bootLogaAlteracoes()
    {
        static::created(function ($model) {
            $descricao = 'Proposta criada.';
            if ($model instanceof \App\Models\Resultado) {
                $descricao = 'Relatório de resultados criado.';
            }
            $model->registrarLog('CRIACAO', $descricao);
        });

        static::updating(function ($model) {
            $alteracoes = $model->getDirty(); // Pega apenas os campos que foram modificados
            $originais = $model->getOriginal(); // Pega os valores antigos dos campos

            // ---- LÓGICA ATUALIZADA ----

            // 1. Trata a mudança de STATUS, incluindo "Voltar para Edição"
            if (isset($alteracoes['status'])) {
                $novoStatus = $alteracoes['status'];
                $statusAntigo = $originais['status'] ?? 'nenhum';

                // Ação específica para quando volta para edição
                if ($novoStatus === 'editando') {
                    $acao = ($model instanceof \App\Models\Resultado) ? 'RESULTADO_REVERTIDO' : 'PROPOSTA_REVERTIDA';
                    $descricao = ($model instanceof \App\Models\Resultado)
                        ? "O relatório de resultados foi revertido para o modo de edição."
                        : "A proposta foi revertida para o modo de edição.";
                    $model->registrarLog($acao, $descricao);
                } else {
                    // Log genérico para outras mudanças de status
                    $model->registrarLog('STATUS_ALTERADO', "Status alterado de '{$statusAntigo}' para '{$novoStatus}'.");
                }
            }
            
            // 2. Trata pareceres (lógica existente mantida)
            if (isset($alteracoes['aprovado_coordenador']) && $alteracoes['aprovado_coordenador'] !== 'pendente') {
                $status = $alteracoes['aprovado_coordenador'] === 'sim' ? 'Aprovado' : 'Reprovado';
                $descricao = "Coordenação: $status. Motivo: " . ($model->motivo_coordenador ?? 'N/A');
                $model->registrarLog('PARECER_COORDENACAO', $descricao);
            }

            // 3. Trata mudança de ETAPA (lógica existente mantida)
            if (isset($alteracoes['etapa'])) {
                // ... (sua lógica para 'etapa' continua aqui) ...
            }

            // 4. NOVO: Log genérico para qualquer outra EDIÇÃO
            // Verifica se houve outras alterações além das que já tratamos
            $camposJaTratados = ['status', 'aprovado_coordenador', 'etapa', 'updated_at'];
            $outrasAlteracoes = array_diff_key($alteracoes, array_flip($camposJaTratados));

            if (!empty($outrasAlteracoes)) {
                $tipoModelo = ($model instanceof \App\Models\Resultado) ? 'O relatório de resultados' : 'A proposta';
                $model->registrarLog('EDICAO', "{$tipoModelo} foi atualizado(a).");
            }
        });
    }

    public function registrarLog(string $acao, string $descricao, $model = null)
    {
        // ... (seu método registrarLog permanece o mesmo) ...
        if ($model === null) {
            $model = $this;
        }
        $projetoId = null;
        if ($model instanceof \App\Models\Projeto) {
            $projetoId = $model->id;
        } elseif ($model instanceof \App\Models\Resultado || $model instanceof \App\Models\ProjetoInvitation) {
            $projetoId = $model->projeto_id;
        }
        if (!$projetoId) return;

        ProjetoLog::create([
            'batch_id'      => static::getLogBatchId(),
            'projeto_id'    => $projetoId,
            'user_id'       => Auth::id(),
            'acao'          => $acao,
            'descricao'     => $descricao,
            'loggable_id'   => $model->id,
            'loggable_type' => get_class($model),
        ]);
    }
}