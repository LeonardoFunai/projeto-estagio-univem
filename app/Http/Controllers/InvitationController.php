<?php

// app/Http/Controllers/InvitationController.php

namespace App\Http\Controllers;

use App\Models\ProjetoInvitation;
use Illuminate\Http\Request;
use App\Notifications\ConviteAceito;
use App\Notifications\ConviteRecusado;

class InvitationController extends Controller
{
    /**
     * Mostra os convites pendentes do usuário logado.
     */
    public function index()
    {
        $convites = ProjetoInvitation::where('email', auth()->user()->email)
                                    ->where('status', 'pendente')
                                    ->with('projeto', 'inviter')
                                    ->latest()
                                    ->get();
                                    
        return view('convites.index', compact('convites'));
    }

    /**
     * Aceita um convite.
     */
    public function aceitar(ProjetoInvitation $invitation)
    {
        // Garante que o usuário logado é o destinatário do convite e que ele está pendente
        if ($invitation->email !== auth()->user()->email || $invitation->status !== 'pendente') {
            abort(403, 'Ação não autorizada.');
        }

        $projeto = $invitation->projeto;
        $user = auth()->user();

        // Adiciona o usuário ao projeto, se ele já não estiver
        if (!$projeto->users()->where('user_id', $user->id)->exists()) {
            $projeto->users()->attach($user->id);
        }

        // Atualiza o status do convite
        $invitation->status = 'aceito';
        $invitation->save();

        $quemConvidou = $invitation->inviter;
        if ($quemConvidou) {
            $quemConvidou->notify(new ConviteAceito($invitation));
        }

        $nomeDoUsuario = $user->name;
            $projeto->registrarLog(
                'PARTICIPANTE_ACEITO', 
                "O usuário {$nomeDoUsuario} aceitou o convite para participar do projeto.",
                $invitation
            );
        return redirect()->route('projetos.show', $projeto)->with('success', 'Convite aceito! Você agora faz parte do projeto.');
    }

    /**
     * Recusa um convite.
     */
    public function recusar(ProjetoInvitation $invitation)
    {
        if ($invitation->email !== auth()->user()->email || $invitation->status !== 'pendente') {
            abort(403, 'Ação não autorizada.');
        }

        $invitation->status = 'recusado';
        $invitation->save();
        $quemConvidou = $invitation->inviter;
        if ($quemConvidou) {
            $quemConvidou->notify(new ConviteRecusado($invitation));
        }

        $projeto = $invitation->projeto;
        $nomeDoUsuario = auth()->user()->name; 
        
        $projeto->registrarLog(
            'PARTICIPANTE_RECUSADO', 
            "O usuário {$nomeDoUsuario} recusou o convite para participar do projeto.",
            $invitation 
        );

        return redirect()->route('convites.index')->with('success', 'Convite recusado.');
    }
}
