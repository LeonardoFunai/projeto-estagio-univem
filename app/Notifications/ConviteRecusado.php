<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProjetoInvitation;

class ConviteRecusado extends Notification
{
    use Queueable;

    protected $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjetoInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $tituloProjeto = $this->invitation->projeto->titulo;

        return [
            'titulo' => 'Convite Recusado',
            'mensagem' => "O usuário com o email {$this->invitation->email} recusou seu convite para o projeto '{$tituloProjeto}'.",
            'url' => route('projetos.show', $this->invitation->projeto_id),
        ];
    }
}