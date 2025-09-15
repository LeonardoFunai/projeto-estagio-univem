<?php

namespace App\Traits;

use App\Models\ProjetoLog;
use Illuminate\Support\Facades\Auth;

trait LogaAlteracoes
{
    protected static function bootLogaAlteracoes()
    {
        static::updating(function ($model) {
            $alteracoes = $model->getDirty();

            if (isset($alteracoes['status'])) {
                $model->registrarLog(
                    'STATUS_ALTERADO',
                    "Status alterado de '{$model->getOriginal('status')}' para '{$alteracoes['status']}'."
                );
            }

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
            'projeto_id'    => $projetoId,
            'user_id'       => Auth::id(),
            'acao'          => $acao,
            'descricao'     => $descricao,
            'loggable_id'   => $this->id,
            'loggable_type' => get_class($this),
        ]);
    }
}