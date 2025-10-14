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
        if ($user->role === 'admin') {
            return true;
        }
        if ($resultado->projeto->users()->where('users.id', $user->id)->exists()) {
            return true;
        }
        $visibleStatuses = ['entregue', 'aprovado', 'reprovado','Finalizado'];

        if (in_array($resultado->status, $visibleStatuses)) {

            if ($user->role === 'napex') {
                return true;
            }

            if ($user->role === 'coordenador') {
                $cursoDoProjetoId = $resultado->projeto->user->curso_id;
                if (!$cursoDoProjetoId) {
                    return false;
                }
                $coordenadorCursosIds = $user->cursosCoordenados->pluck('id')->toArray();
                return in_array($cursoDoProjetoId, $coordenadorCursosIds);
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
        if (!in_array($resultado->status, ['editando', 'reprovado'])) {
            return false;
        }

        return $this->update($user, $resultado);
    }
    
    /**
     * Determina se o usuário pode reverter o resultado para rascunho.
     */
    public function revertToDraft(User $user, Resultado $resultado): bool
    {
        if ($resultado->status !== 'entregue' || $resultado->aprovado_napex !== 'pendente' || $resultado->aprovado_coordenador !== 'pendente') {
            return false;
        }

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

        if ($resultado->status !== 'entregue') {
            return false;
        }

        return in_array($user->role, ['napex', 'coordenador']);
    }
}