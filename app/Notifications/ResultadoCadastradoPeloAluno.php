<?php

namespace App\Notifications;

use App\Models\Resultado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultadoCadastradoPeloAluno extends Notification
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
        $alunoNome = $projeto->user ? $projeto->user->name : 'Seu orientado(a)';

        return [
            'projeto_id' => $projeto->id,
            'titulo_projeto' => $projeto->titulo,
            'mensagem' => "{$alunoNome} cadastrou o relatório de resultados para o projeto '{$projeto->titulo}'.",
            'url' => route('resultados.show', $this->resultado->id),
        ];
    }
}