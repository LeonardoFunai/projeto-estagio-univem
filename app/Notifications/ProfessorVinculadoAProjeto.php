<?php

namespace App\Notifications;

use App\Models\Projeto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfessorVinculadoAProjeto extends Notification
{
    use Queueable;

    protected $projeto;

    public function __construct(Projeto $projeto)
    {
        $this->projeto = $projeto;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $alunoNome = $this->projeto->user ? $this->projeto->user->name : 'Um aluno';
        $tituloProjeto = $this->projeto->titulo;

        return [
            'projeto_id' => $this->projeto->id,
            'titulo_projeto' => $tituloProjeto,
            'mensagem' => "Você foi adicionado como orientador na proposta '{$tituloProjeto}' por {$alunoNome}.",
            'url' => route('projetos.show', $this->projeto->id),
        ];
    }
}