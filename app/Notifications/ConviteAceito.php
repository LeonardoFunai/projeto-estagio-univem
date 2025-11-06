<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProjetoInvitation;

class ConviteAceito extends Notification
{
    use Queueable;

    protected $invitation;
    protected $convidado;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjetoInvitation $invitation)
    {
        $this->invitation = $invitation;
        // Pega o usuário que aceitou o convite
        $this->convidado = \App\Models\User::where('email', $invitation->email)->first();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Salva a notificação no banco de dados
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $nomeConvidado = $this->convidado ? $this->convidado->name : $this->invitation->email;
        $tituloProjeto = $this->invitation->projeto->titulo;

        return [
            'titulo' => 'Convite Aceito!',
            'mensagem' => "Boas notícias! {$nomeConvidado} aceitou seu convite e agora faz parte do projeto '{$tituloProjeto}'.",
            'url' => route('projetos.show', $this->invitation->projeto_id), // Link para a página de edição do projeto
        ];
    }
}