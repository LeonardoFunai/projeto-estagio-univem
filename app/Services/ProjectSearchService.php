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
    public function buildQuery(array $filters)
    {
        $query = Projeto::query()->with(['user.curso', 'resultado', 'users']);
        $user = Auth::user();

        if ($user->role === 'aluno') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('users', function ($uq) use ($user) {
                      $uq->where('users.id', $user->id);
                  });
            });
        }

        if ($user->role === 'professor') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        if (!empty($filters['curso_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('curso_id', $filters['curso_id']);
            });
        }

        if (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');

            if ($cursosCoordenadosIds->isNotEmpty()) {
                $query->whereHas('user', function ($q) use ($cursosCoordenadosIds) {
                    $q->whereIn('curso_id', $cursosCoordenadosIds);
                });
            } else {
                // Se um coordenador não estiver associado a nenhum curso, ele não verá nenhum projeto.
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('titulo', 'like', $searchTerm)
                  ->orWhere('status', 'like', $searchTerm)
                  ->orWhere('etapa', 'like', $searchTerm);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', 'desc');

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

        // Professor: vê os projetos nos quais ele está vinculado como participante
        if (str_starts_with($user->role, 'professor')) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        
        // Coordenador: Vê todos os projetos dos cursos que coordena
        if (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            if ($cursosCoordenadosIds->isNotEmpty()) {
                $query->whereHas('user', function ($q) use ($cursosCoordenadosIds) {
                    $q->whereIn('curso_id', $cursosCoordenadosIds);
                });
            } else {
                // Se não coordena cursos, não vê nenhum projeto
                $query->whereRaw('1 = 0');
            }
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