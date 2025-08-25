<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjetoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode ver a lista de projetos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina se o usuário pode ver os detalhes de um projeto específico.
     */
    public function view(User $user, Projeto $projeto): bool
    {
        if (in_array($user->role, ['napex', 'coordenador'])) {
            return true;
        }
        if ($user->role === 'aluno' && $user->id === $projeto->user_id) {
            return true;
        }
        if ($user->role === 'professor' && $projeto->professores()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determina se o usuário pode criar um novo projeto.
     */
    public function create(User $user): bool
    {
        return $user->role === 'aluno';
    }

    /**
     * Determina se o usuário pode ATUALIZAR (editar) os dados de um projeto.
     */
    public function update(User $user, Projeto $projeto): bool
    {
        if ($user->role === 'aluno') {
            return $user->id === $projeto->user_id && $projeto->status === 'editando';
        }

        if ($user->role === 'professor') {
            return $projeto->professores()->where('user_id', $user->id)->exists() && $projeto->status === 'editando';
        }

        return false;
    }

    /**
     * Determina se o usuário pode deletar um projeto.
     */
    public function delete(User $user, Projeto $projeto): bool
    {
        if ($user->role === 'aluno' && $user->id === $projeto->user_id) {
            return !in_array($projeto->status, ['aprovado', 'entregue']);
        }
        return false;
    }

    /**
     * Determina se o usuário pode enviar o projeto para avaliação.
     */
    public function submit(User $user, Projeto $projeto): bool
    {
        return $user->role === 'aluno' && $user->id === $user->id && $projeto->status === 'editando';
    }
    
    /**
     * NOVO: Determina se um usuário pode reverter um projeto para o estado de 'edição'.
     */
    public function revertToEditing(User $user, Projeto $projeto): bool
    {
        // 1. O projeto deve estar no estado correto para ser revertido:
        //    - Status 'entregue'
        //    - E ainda não pode ter sido aprovado por nenhum dos avaliadores.
        $isRevertableState = $projeto->status === 'entregue' &&
                            $projeto->aprovado_napex !== 'sim' &&
                            $projeto->aprovado_coordenador !== 'sim';

        if (!$isRevertableState) {
            return false;
        }

        // 2. Apenas o aluno que criou ou um professor vinculado podem reverter.
        if ($user->role === 'aluno' && $user->id === $projeto->user_id) {
            return true;
        }
        
        if ($user->role === 'professor' && $projeto->professores()->where('user_id', $user->id)->exists()) {
            return true;
        }
        
        // Se não for nenhum dos casos acima, a permissão é negada.
        return false;
    }

    // Dentro de app/Policies/ProjetoPolicy.php
    public function exportGeneralPdf(User $user): bool
    {
        // Apenas estes perfis podem exportar o relatório geral
        return in_array($user->role, ['napex', 'coordenador', 'admin']);
    }

    /**
     * Determina se o usuário pode avaliar o projeto como NAPEX.
     */
    public function approveByNapex(User $user, Projeto $projeto): bool
    {
        return $user->role === 'napex' && $projeto->status === 'entregue';
    }

    /**
     * Determina se o usuário pode avaliar o projeto como Coordenador.
     */
    public function approveByCoordinator(User $user, Projeto $projeto): bool
    {
        return $user->role === 'coordenador' && $projeto->status === 'entregue';
    }
}