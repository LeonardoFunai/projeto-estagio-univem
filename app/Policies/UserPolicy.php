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
        // Napex e Coordenadores podem editar seus próprios perfis.
        if ($user->id === $model->id) {
            return true;
        }

        // Napex e Coordenadores podem editar apenas alunos.
        if (in_array($user->role, ['napex', 'coordenador'])) {
            return $model->role === 'aluno';
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if (in_array($user->role, ['napex', 'coordenador'])) {
            return $model->role === 'aluno';
        }
        
        return false;
    }
}