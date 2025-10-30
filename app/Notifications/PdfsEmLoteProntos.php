<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PdfsEmLoteProntos extends Notification implements ShouldQueue
{
    use Queueable;

    public $downloadUrl;
    public $count;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $downloadUrl, int $count)
    {
        $this->downloadUrl = $downloadUrl;
        $this->count = $count;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Envia para o banco de dados (ícone de sino) e por e-mail
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Seu lote de PDFs está pronto')
                    ->line("O arquivo .zip contendo {$this->count} PDFs de projetos foi gerado com sucesso.")
                    ->action('Baixar ZIP', $this->downloadUrl)
                    ->line('O link expirará em 24 horas.'); // Recomendação
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => 'Lote de PDFs Pronto',
            'mensagem' => "Seu .zip com {$this->count} PDFs está pronto para download.",
            'url' => $this->downloadUrl,
        ];
    }
}