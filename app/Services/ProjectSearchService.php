<?php

namespace App\Services;

use App\Models\Projeto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProjectSearchService
{
    public function buildQuery(array $filters): Builder
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
        elseif ($user->role === 'professor') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        elseif (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            
            $query->where(function ($q) use ($user, $cursosCoordenadosIds) {
                
                if ($cursosCoordenadosIds->isNotEmpty()) {
                    $q->where(function($subq) use ($cursosCoordenadosIds) {
                        $subq->whereHas('user', function ($uq) use ($cursosCoordenadosIds) {
                            $uq->whereIn('curso_id', $cursosCoordenadosIds);
                        })->where('status', '!=', 'editando');
                    });
                }

                $q->orWhereHas('users', function ($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                });
            });

        }
        elseif ($user->role === 'napex') {
            $query->where('status', '!=', 'editando');
        }
        elseif ($user->role === 'admin') {
            // Nenhuma restrição
        }   
        else {
            $query->whereRaw('1 = 0');
        }


        // --- LÓGICA DE FILTROS CORRIGIDA E UNIFICADA ---

        // Filtro de Etapa
        if (!empty($filters['etapa'])) {
            $query->where('etapa', $filters['etapa']);
        }

        // Filtro de Status
        if (!empty($filters['status'])) {
            $status = $filters['status'];
            $query->where(function ($q) use ($status) {
                // Condição 1: Etapa 'Proposta' E status da proposta corresponde
                $q->where(function ($subq) use ($status) {
                    $subq->where('etapa', 'Proposta')
                         ->where('status', $status);
                })
                // OU Condição 2: Etapa 'Resultado' E status do resultado corresponde
                ->orWhere(function ($subq) use ($status) {
                    $subq->where('etapa', 'Resultado')
                         ->whereHas('resultado', function ($res) use ($status) {
                             $res->where('status', $status);
                         });
                })
                // OU Condição 3: Etapa 'Concluído' E o status buscado é 'Finalizado'
                ->orWhere(function ($subq) use ($status) {
                    if (strtolower($status) === 'finalizado') {
                        $subq->where('etapa', 'Concluído');
                    }
                });
            });
        }

        // Filtro de Aprovação NAPEX
        if (isset($filters['aprovado_napex']) && $filters['aprovado_napex'] !== '') {
            $aprovacao = $filters['aprovado_napex'];
            $query->where(function ($q) use ($aprovacao) {
                // Condição 1: Etapa 'Proposta' E aprovação da proposta corresponde
                $q->where(function ($subq) use ($aprovacao) {
                    $subq->where('etapa', 'Proposta')
                         ->where('aprovado_napex', $aprovacao);
                })
                // OU Condição 2: Etapa 'Resultado' E aprovação do resultado corresponde
                ->orWhere(function ($subq) use ($aprovacao) {
                    $subq->where('etapa', 'Resultado')
                         ->whereHas('resultado', function ($res) use ($aprovacao) {
                             $res->where('aprovado_napex', $aprovacao);
                         });
                })
                // OU Condição 3: Etapa 'Concluído' (que é sempre 'sim')
                ->orWhere(function ($subq) use ($aprovacao) {
                    if ($aprovacao === 'sim') {
                        $subq->where('etapa', 'Concluído');
                    }
                });
            });
        }
        
        // Filtro de Aprovação Coordenador
        if (isset($filters['aprovado_coordenador']) && $filters['aprovado_coordenador'] !== '') {
            $aprovacao = $filters['aprovado_coordenador'];
            $query->where(function ($q) use ($aprovacao) {
                // Condição 1: Etapa 'Proposta' E aprovação da proposta corresponde
                $q->where(function ($subq) use ($aprovacao) {
                    $subq->where('etapa', 'Proposta')
                         ->where('aprovado_coordenador', $aprovacao);
                })
                // OU Condição 2: Etapa 'Resultado' E aprovação do resultado corresponde
                ->orWhere(function ($subq) use ($aprovacao) {
                    $subq->where('etapa', 'Resultado')
                         ->whereHas('resultado', function ($res) use ($aprovacao) {
                             $res->where('aprovado_coordenador', $aprovacao);
                         });
                })
                // OU Condição 3: Etapa 'Concluído' (que é sempre 'sim')
                ->orWhere(function ($subq) use ($aprovacao) {
                    if ($aprovacao === 'sim') {
                        $subq->where('etapa', 'Concluído');
                    }
                });
            });
        }
        
        if (!empty($filters['curso_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('curso_id', $filters['curso_id']);
            });
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('titulo', 'like', $searchTerm);
            });
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

        $query->orderBy('created_at', 'desc');

        return $query;
    }
}





