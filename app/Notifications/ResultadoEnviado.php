<?php

namespace App\Notifications;

use App\Models\Resultado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ResultadoEnviado extends Notification
{
    use Queueable;

    protected $resultado;

    public function __construct(Resultado $resultado)
    {
        $this->resultado = $resultado;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $projeto = $this->resultado->projeto;
        $alunoNome = $projeto->user ? $projeto->user->name : 'Aluno';

        return [
            'projeto_id' => $projeto->id,
            'titulo_projeto' => $projeto->titulo,
            'mensagem' => "O relatório de resultados de '{$projeto->titulo}', enviado por {$alunoNome}, aguarda avaliação.",
            'url' => route('resultados.show', $this->resultado->id),
        ];
    }
}