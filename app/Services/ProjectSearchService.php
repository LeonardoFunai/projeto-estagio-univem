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
    // app/Services/ProjectSearchService.php

    public function buildQuery(array $filters)
    {
        $query = Projeto::query()->with(['user.curso', 'resultado', 'users']);
        $user = Auth::user();

        // --- LÓGICA DE VISIBILIDADE BASEADA NO PERFIL ---

        if ($user->role === 'aluno') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhereHas('users', function ($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                });
            });
        } 
        elseif ($user->role === 'professor') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        // --- CORREÇÃO PRINCIPAL PARA COORDENADOR E NAPEX ---
        elseif (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            
            // Inicia um grupo de condições para o coordenador
            $query->where(function ($q) use ($user, $cursosCoordenadosIds) {
                
                // Condição 1: Vê projetos dos seus cursos que NÃO ESTÃO em edição
                if ($cursosCoordenadosIds->isNotEmpty()) {
                    $q->where(function($subq) use ($cursosCoordenadosIds) {
                        $subq->whereHas('user', function ($uq) use ($cursosCoordenadosIds) {
                            $uq->whereIn('curso_id', $cursosCoordenadosIds);
                        })->where('status', '!=', 'editando');
                    });
                }

                // Condição 2 (OU): Vê projetos em que é participante (professor), independentemente do status
                $q->orWhereHas('users', function ($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                });
            });

        }
        elseif ($user->role === 'napex') {
            // NAPEX só vê projetos que não estão mais em edição
            $query->where('status', '!=', 'editando');
        }
        elseif ($user->role === 'admin') {
            // Nenhuma restrição de visibilidade aplicada
        }   
        else {
            // Para qualquer outro papel, não retorna projetos
            $query->whereRaw('1 = 0'); // Condição impossível
        }

        // --- Lógica de Filtros (continua a mesma) ---
        if (!empty($filters['curso_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('curso_id', $filters['curso_id']);
            });
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

        if (!empty($filters['etapa'])) {
            $query->where('etapa', $filters['etapa']);
        }

        if (!empty($filters['titulo'])) {
            $query->where('titulo', 'like', '%' . $filters['titulo'] . '%');
        }

        if (!empty($filters['data_inicio_de'])) {
            $query->whereDate('data_inicio', '>=', $filters['data_inicio_de']);
        }

        if (!empty($filters['data_inicio_ate'])) {
            $query->whereDate('data_inicio', '<=', $filters['data_inicio_ate']);
        }

        if (!empty($filters['data_fim_de'])) {
            $query->whereDate('data_fim', '>=', $filters['data_fim_de']);
        }

        if (!empty($filters['data_fim_ate'])) {
            $query->whereDate('data_fim', '<=', $filters['data_fim_ate']);
        }

        if (!empty($filters['aprovado_napex'])) {
            $query->where('aprovado_napex', $filters['aprovado_napex']);
        }

        if (!empty($filters['aprovado_coordenador'])) {
            $query->where('aprovado_coordenador', $filters['aprovado_coordenador']);
        }

        $query->orderBy('created_at', 'desc');

        return $query;
    }
}