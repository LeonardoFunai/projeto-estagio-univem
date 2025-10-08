<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'napex', 'coordenador']);
    }

    public function view(User $user, User $model): bool
    {
        return in_array($user->role, ['admin', 'napex', 'coordenador']);
    }

    /**
     * Determina se o usuário pode criar usuários.
     * A lógica específica de qual tipo de usuário pode ser criado
     * será tratada no UserController.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'napex', 'coordenador']);
    }

    /**
     * Determina se o usuário pode atualizar um usuário.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->role === 'admin') {
            return $user->id !== $model->id;
        }

        if ($user->role === 'napex') {
            return $model->role === 'aluno';
        }

        if ($user->role === 'coordenador') {
            if ($model->role !== 'aluno') {
                return false;
            }
            $coordenadorCursosIds = $user->cursosCoordenados->pluck('id')->toArray();
            return in_array($model->curso_id, $coordenadorCursosIds);
        }

        // Por padrão, nega a permissão.
        return false;
    }


    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        if ($user->role === 'napex' && $model->role === 'coordenador') {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'napex') {
            return true;
        }

        if ($user->role === 'coordenador' && $model->role === 'aluno') {
            $coordenadorCursosIds = $user->cursosCoordenados->pluck('id')->toArray();
            return in_array($model->curso_id, $coordenadorCursosIds);
        }

        return false;
    }
}