<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Projeto; // Importe o modelo Projeto

class PropostaEnviada extends Notification
{
    use Queueable;

    protected $projeto;

    /**
     * Create a new notification instance.
     */
    public function __construct(Projeto $projeto)
    {
        $this->projeto = $projeto;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {

        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $alunoNome = $this->projeto->user->name;
        $tituloProjeto = $this->projeto->titulo;

        return [
            'projeto_id' => $this->projeto->id,
            'titulo_projeto' => $tituloProjeto,
            'mensagem' => "A proposta do projeto '{$tituloProjeto}' foi enviada por {$alunoNome} e aguarda avaliação.",
            'url' => route('projetos.show', $this->projeto->id), // Link para ver o projeto
        ];
    }

    /**
     * (Opcional) Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('projetos.show', $this->projeto->id);
        $alunoNome = $this->projeto->user->name;
        $tituloProjeto = $this->projeto->titulo;

        return (new MailMessage)
                    ->subject('Nova Proposta de Projeto para Avaliação')
                    ->greeting('Olá!')
                    ->line("A proposta do projeto '{$tituloProjeto}', submetida por {$alunoNome}, está pronta para ser avaliada.")
                    ->action('Ver Proposta', $url)
                    ->line('Obrigado por usar nosso sistema.');
    }
}