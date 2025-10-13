<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjetoPolicy
{
    use HandlesAuthorization;

    /**
     * Permite que administradores executem qualquer ação.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return null; // Deixa outras regras decidirem
    }

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
// app/Policies/ProjetoPolicy.php

    public function view(User $user, Projeto $projeto): bool
    {
        // REGRA 1: Admin pode ver tudo.
        if ($user->role === 'admin') {
            return true;
        }

        // REGRA 2: Se o usuário for um participante do projeto (aluno ou professor/coordenador),
        // ele sempre poderá visualizar.
        if ($projeto->users()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // REGRA 3: Se não for participante, verifica se é um avaliador com permissão.

        // NAPEx só pode ver se o status NÃO for 'editando'.
        if ($user->role === 'napex') {
            return $projeto->status !== 'editando';
        }

        // Coordenador só pode ver se o projeto for do seu curso E o status NÃO for 'editando'.
        if (str_starts_with($user->role, 'coordenador')) {
            // Bloqueia imediatamente se o status for 'editando' (e ele não for participante, verificado na Regra 2)
            if ($projeto->status === 'editando') {
                return false;
            }
            
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            // Permite se o curso do proponente do projeto estiver na lista de cursos do coordenador.
            return $cursosCoordenadosIds->contains($projeto->user->curso_id);
        }

        // Nega o acesso por padrão para qualquer outro caso.
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
        // Força a recarga dos dados do projeto a partir do banco de dados.
        // Isso garante que estamos verificando o status mais atual.
        $projeto->refresh();

        // Regra 1: Se o usuário for 'admin', permite a edição imediatamente.
        if ($user->role === 'admin') {
            return true;
        }
   
        // Regra 2: Para todos os outros, a permissão só é concedida se
        // o status for 'editando' E o usuário for um participante.
        if ($projeto->status === 'editando') {
            return $projeto->users()->where('user_id', $user->id)->exists();
        }
        
        // Se nenhuma condição for atendida, nega a permissão.
        return false;
    }

    /**
     * Determina se o usuário pode deletar um projeto.
     */
    public function delete(User $user, Projeto $projeto): bool
    {
        // Apenas o criador original do projeto pode deletar, e somente se não estiver aprovado/entregue.
        if ($user->id === $projeto->user_id) {
            return !in_array($projeto->status, ['aprovado', 'entregue']);
        }
        return false;
    }

    /**
     * Determina se o usuário pode enviar o projeto para avaliação.
     */
    public function submit(User $user, Projeto $projeto): bool
    {
        // Apenas participantes com o perfil 'aluno' podem enviar, e somente se o status for 'editando'.
        if ($projeto->status === 'editando' && $user->role === 'aluno') {
            return $projeto->users()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    /**
     * Determina se um usuário pode reverter um projeto para o estado de 'edição'.
     */
    public function revertToEditing(User $user, Projeto $projeto): bool
    {
        // Apenas projetos 'entregues' e ainda não avaliados podem ser revertidos.
        $isRevertableState = $projeto->status === 'entregue' &&
                             $projeto->aprovado_napex === 'pendente' &&
                             $projeto->aprovado_coordenador === 'pendente';

        if (!$isRevertableState) {
            return false;
        }

        // NAPEX, Coordenadores e participantes do projeto podem reverter.
        if (in_array($user->role, ['napex', 'coordenador'])) {
            if (str_starts_with($user->role, 'coordenador')) {
                $cursoDoProjeto = $projeto->user->curso;
                if (!$cursoDoProjeto) {
                    return false;
                }
                return $user->cursosCoordenados()->where('curso_id', $cursoDoProjeto->id)->exists();
            }
            return true;
        }

        return $projeto->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determina se o usuário pode exportar o relatório geral em PDF.
     */
    public function exportGeneralPdf(User $user): bool
    {
        return in_array($user->role, ['napex', 'coordenador']);
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
        // Regra 1: O usuário DEVE ter a role 'coordenador' e o projeto DEVE estar 'entregue'.
        if (!str_starts_with($user->role, 'coordenador') || $projeto->status !== 'entregue') {
            return false;
        }

        // Regra 2: O projeto precisa ter um proponente (user) com um curso associado.
        $cursoDoProjeto = $projeto->user->curso;
        if (!$cursoDoProjeto) {
            return false; // Nega se o proponente não tiver curso.
        }

        // Regra 3: Verifica se o ID do curso do projeto está na lista de cursos que este usuário coordena.
        return $user->cursosCoordenados()->where('curso_id', $cursoDoProjeto->id)->exists();
    }
}