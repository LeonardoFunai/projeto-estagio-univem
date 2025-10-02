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
        static::updating(function ($model) {
            $alteracoes = $model->getDirty();

            // Log de alteração de status
            if (isset($alteracoes['status'])) {
                $model->registrarLog(
                    'STATUS_ALTERADO',
                    "Status alterado de '{$model->getOriginal('status')}' para '{$alteracoes['status']}'."
                );
            }
            
            // --- ADICIONE O NOVO BLOCO AQUI ---
            // Log do parecer do Coordenador
            if (isset($alteracoes['aprovado_coordenador']) && $alteracoes['aprovado_coordenador'] !== 'pendente') {
                $status = $alteracoes['aprovado_coordenador'] === 'sim' ? 'Aprovado' : 'Reprovado';
                $descricao = "Coordenação: $status. Motivo: " . ($model->motivo_coordenador ?? 'N/A');
                $model->registrarLog('PARECER_COORDENACAO', $descricao);
            }
            // --- FIM DO NOVO BLOCO ---

            // Log de alteração de etapa
            if (isset($alteracoes['etapa'])) {
                if ($model->getOriginal('etapa') === 'Resultado' && $alteracoes['etapa'] === 'Concluído') {
                    return;
                }
                $model->registrarLog(
                    'ETAPA_ALTERADA',
                    "Etapa alterada de '{$model->getOriginal('etapa')}' para '{$alteracoes['etapa']}'."
                );
            }
        });

        static::created(function ($model) {
            $descricao = 'Proposta criada.';
            if ($model instanceof \App\Models\Resultado) {
                $descricao = 'Relatório de resultados criado.';
            }
            $model->registrarLog('CRIACAO', $descricao);
        });
    }

    public function registrarLog(string $acao, string $descricao)
    {
        $projetoId = $this instanceof \App\Models\Resultado ? $this->projeto_id : $this->id;

        ProjetoLog::create([
            'batch_id'      => static::getLogBatchId(),
            'projeto_id'    => $projetoId,
            'user_id'       => Auth::id(),
            'acao'          => $acao,
            'descricao'     => $descricao,
            'loggable_id'   => $this->id,
            'loggable_type' => get_class($this),
        ]);
    }
}