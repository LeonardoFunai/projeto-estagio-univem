<?php

namespace App\Notifications;

use App\Models\Resultado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultadoAvaliado extends Notification
{
    use Queueable;

    protected $resultado;
    protected $parecer; // 'Aprovado' ou 'Recusado'
    protected $motivo;
    protected $avaliador;

    /**
     * Create a new notification instance.
     */
    public function __construct(Resultado $resultado, string $parecer, ?string $motivo = null, string $avaliador)
    {
        $this->resultado = $resultado;
        $this->parecer = $parecer;
        $this->motivo = $motivo;
        $this->avaliador = $avaliador;
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
    public function toDatabase(object $notifiable): array
    {
        $projeto = $this->resultado->projeto;
        $mensagem = "Seu relatório de '{$projeto->titulo}' recebeu o parecer '{$this->parecer}' do(a) {$this->avaliador}.";

        if ($this->parecer === 'Recusado') {
            $mensagem .= " Clique para ver os detalhes.";
        }

        return [
            'projeto_id' => $projeto->id,
            'titulo_projeto' => $projeto->titulo,
            'mensagem' => $mensagem,
            'url' => route('resultados.show', $this->resultado->id),
        ];
    }
}