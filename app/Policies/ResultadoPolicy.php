<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\Resultado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResultadoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode ver um relatório de resultado.
     */

    public function view(User $user, Resultado $resultado): bool
    {
        // Admin pode ver tudo.
        if ($user->role === 'admin') {
            return true;
        }

        if ($resultado->projeto->users()->where('users.id', $user->id)->exists()) {
            return true;
        }

        if ($user->role === 'napex') {
            return in_array($resultado->status, ['entregue', 'aprovado']);
        }

        if ($user->role === 'coordenador') {
            if (in_array($resultado->status, ['entregue', 'aprovado'])) {
                $coordenadorCursosIds = $user->cursosCoordenados->pluck('id')->toArray();
                return in_array($resultado->projeto->curso_id, $coordenadorCursosIds);
            }
        }

        return false;
    }

    /**
     * Determina se o usuário pode criar um novo relatório de resultado.
     * Apenas o aluno criador do projeto pode.
     */
    public function create(User $user, Projeto $projeto): bool
    {

        return $projeto->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determina se o usuário pode atualizar (editar) um relatório de resultado.
     */
    public function update(User $user, Resultado $resultado): bool
    {

        if (!in_array($resultado->status, ['editando', 'reprovado'])) {
            return false;
        }

        return $resultado->projeto->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determina se o usuário pode submeter o resultado para avaliação.
     */
    public function sendForEvaluation(User $user, Resultado $resultado): bool
    {
        // Apenas pode enviar se estiver como rascunho ou reprovado
        if (!in_array($resultado->status, ['editando', 'reprovado'])) {
            return false;
        }

        // O usuário precisa ser o criador (aluno) ou um professor vinculado.
        return $this->update($user, $resultado);
    }
    
    /**
     * Determina se o usuário pode reverter o resultado para rascunho.
     */
    public function revertToDraft(User $user, Resultado $resultado): bool
    {
        // 1. O resultado precisa estar no estado correto para ser revertido.
        if ($resultado->status !== 'entregue' || $resultado->aprovado_napex !== 'pendente' || $resultado->aprovado_coordenador !== 'pendente') {
            return false;
        }

        // 2. O usuário precisa ser o criador (aluno) ou um professor vinculado.
        if ($user->id === $resultado->projeto->user_id) {
            return true;
        }
        if ($user->role === 'professor' && $resultado->projeto->professores()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determina se o usuário pode avaliar o resultado.
     */
    public function evaluate(User $user, Resultado $resultado): bool
    {
        // 1. O resultado deve estar 'entregue' para ser avaliado.
        if ($resultado->status !== 'entregue') {
            return false;
        }

        // 2. Apenas NAPEX e Coordenador podem avaliar.
        return in_array($user->role, ['napex', 'coordenador']);
    }
}