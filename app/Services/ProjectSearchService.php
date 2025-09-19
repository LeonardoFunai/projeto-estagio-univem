<?php

namespace App\Services;

use App\Models\Projeto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProjectSearchService
{
    /**
     * Retorna uma instância da query de projetos com filtros e regras de permissão aplicados.
     *
     * @param array $filters Os filtros vindos da requisição.
     * @return Builder
     */
    public function buildQuery(array $filters): Builder
    {
        $query = Projeto::with(['atividades', 'user', 'professores', 'resultado']);
        $user = Auth::user();

        // 1. Aplica as regras de visualização de acordo com o perfil do usuário
        $this->applyRoleBasedVisibility($query, $user);

        // 2. Aplica os filtros de busca do formulário
        $this->applyRequestFilters($query, $filters);

        // 3. Aplica a ordenação
        $this->applyOrdering($query, $filters);

        return $query;
    }

    /**
     * Aplica as regras de visibilidade baseadas no perfil do usuário logado.
     */
    private function applyRoleBasedVisibility(Builder $query, $user): void
    {
        if ($user->role === 'aluno') {
            $query->where('user_id', $user->id);
        }

        if ($user->role === 'professor') {
            $query->whereHas('professores', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if (in_array($user->role, ['napex', 'coordenador'])) {
            $query->where(function ($q) {
                // Propostas em avaliação ou já aprovadas
                $q->where(function ($sub) {
                    $sub->where('etapa', 'Proposta')->whereIn('status', ['entregue', 'aprovado']);
                })
                // Ou qualquer projeto que já passou para a etapa de Resultado ou foi Concluído
                ->orWhereIn('etapa', ['Resultado', 'Concluído']);
            });
        }
    }

    /**
     * Aplica os filtros vindos do formulário de busca.
     */
    private function applyRequestFilters(Builder $query, array $filters): void
    {
        // Filtro por etapa
        if (!empty($filters['etapa']) && $filters['etapa'] != 'todas') {
            $query->where('etapa', $filters['etapa']);
        }

        // Filtros de texto
        if (!empty($filters['cadastrado_por'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['cadastrado_por'] . '%');
            });
        }
        if (!empty($filters['titulo'])) {
            $query->where('titulo', 'like', '%' . $filters['titulo'] . '%');
        }
        if (!empty($filters['periodo'])) {
            $query->where('periodo', 'like', '%' . $filters['periodo'] . '%');
        }

        // Filtro de status (contextual à etapa)
        if (!empty($filters['status']) && $filters['status'] != 'todos') {
            $status = $filters['status'];
            $etapa = $filters['etapa'] ?? null;

            if ($status === 'finalizado') {
                $query->where('etapa', 'Concluído');
            } elseif ($etapa === 'Resultado') {
                $query->whereHas('resultado', fn($q) => $q->where('status', $status));
            } else {
                $query->where('projetos.status', $status);
            }
        }
        
        // Filtros de aprovação (contextuais à etapa)
        if (isset($filters['aprovado_napex'])) {
             $aprovacao = $filters['aprovado_napex'];
             if (($filters['etapa'] ?? null) === 'Resultado') {
                 $query->whereHas('resultado', fn($q) => $q->where('aprovado_napex', $aprovacao));
             } else {
                 $query->where('aprovado_napex', $aprovacao);
             }
        }
        
        if (isset($filters['aprovado_coordenador'])) {
            $aprovacao = $filters['aprovado_coordenador'];
            if (($filters['etapa'] ?? null) === 'Resultado') {
                $query->whereHas('resultado', fn($q) => $q->where('aprovado_coordenador', $aprovacao));
            } else {
                $query->where('aprovado_coordenador', $aprovacao);
            }
        }

        // Filtros de data
        if (!empty($filters['data_inicio_de']) && !empty($filters['data_inicio_ate'])) {
            $query->whereBetween('data_inicio', [$filters['data_inicio_de'], $filters['data_inicio_ate']]);
        }
        if (!empty($filters['data_fim_de']) && !empty($filters['data_fim_ate'])) {
            $query->whereBetween('data_fim', [$filters['data_fim_de'], $filters['data_fim_ate']]);
        }
        
        // Carga horária
        if (!empty($filters['carga_min'])) {
             // ... sua lógica de carga horária ...
        }
        if (!empty($filters['carga_max'])) {
            // ... sua lógica de carga horária ...
        }
    }

    /**
     * Aplica a ordenação na query.
     */
    private function applyOrdering(Builder $query, array $filters): void
    {
        $ordenar = $filters['ordenar'] ?? 'data_desc';
        if ($ordenar == 'data_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}