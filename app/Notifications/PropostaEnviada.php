<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Projeto; // 1. Importa o modelo Projeto

class PropostaEnviada extends Notification
{
    use Queueable;

    protected $projeto; // 2. Propriedade para guardar os dados do projeto

    /**
     * Create a new notification instance.
     */
    // 3. Construtor modificado para receber o projeto
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
        // 4. Define o canal para 'database' para que apareça no sino
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    // 5. Adiciona o método 'toDatabase' que formata a notificação
    public function toDatabase(object $notifiable): array
    {
        $alunoNome = $this->projeto->user ? $this->projeto->user->name : 'Aluno desconhecido';
        $tituloProjeto = $this->projeto->titulo;

        return [
            'projeto_id' => $this->projeto->id,
            'titulo_projeto' => $tituloProjeto,
            'mensagem' => "A proposta '{$tituloProjeto}' foi enviada por {$alunoNome} e aguarda avaliação.",
            'url' => route('projetos.show', $this->projeto->id),
        ];
    }
    
    /**
     * (Opcional) Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('projetos.show', $this->projeto->id);
        $alunoNome = $this->projeto->user ? $this->projeto->user->name : 'Aluno desconhecido';
        $tituloProjeto = $this->projeto->titulo;

        return (new MailMessage)
                    ->subject('Nova Proposta de Projeto para Avaliação')
                    ->greeting('Olá!')
                    ->line("A proposta do projeto '{$tituloProjeto}', submetida por {$alunoNome}, está pronta para ser avaliada.")
                    ->action('Ver Proposta', $url)
                    ->line('Obrigado por usar nosso sistema.');
    }
}