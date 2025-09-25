<?php

namespace App\Services;

use App\Models\Projeto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProjectSearchService
{
    /**
     * Retorna uma instância da query de projetos com filtros e regras de permissão aplicados.
     */
    public function buildQuery(array $filters): Builder
    {
        // Garante que o relacionamento 'users' seja carregado para evitar N+1 queries.
        $query = Projeto::with(['user', 'users', 'resultado', 'atividades']);
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
        // Aluno: vê apenas os projetos que ele criou
        if ($user->role === 'aluno') {
            $query->where('user_id', $user->id);
        }

        // --- CORREÇÃO APLICADA AQUI ---
        // Professor: vê os projetos nos quais ele está vinculado como participante
        if (str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador')) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        
        // NAPEX: Vê todos os projetos que já foram entregues para avaliação
        if ($user->role === 'napex') {
            $query->where('status', '!=', 'editando');
        }
    }

    /**
     * Aplica os filtros vindos do formulário de busca.
     */
    private function applyRequestFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['titulo'])) {
            $query->where('titulo', 'like', '%' . $filters['titulo'] . '%');
        }
        
        if (!empty($filters['cadastrado_por'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['cadastrado_por'] . '%');
            });
        }

        // Adicione outros filtros conforme necessário
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