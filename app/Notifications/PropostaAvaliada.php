<?php

namespace App\Notifications;

use App\Models\Projeto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropostaAvaliada extends Notification
{
    use Queueable;

    protected $projeto;
    protected $parecer; // 'Aprovado' ou 'Recusado'
    protected $motivo;
    protected $avaliador;

    public function __construct(Projeto $projeto, string $parecer, ?string $motivo = null, string $avaliador)
    {
        $this->projeto = $projeto;
        $this->parecer = $parecer;
        $this->motivo = $motivo;
        $this->avaliador = $avaliador;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tituloProjeto = $this->projeto->titulo;
        $alunoDoProjeto = $this->projeto->user;
        $mensagem = '';

        // ===== LÓGICA DE MENSAGEM PERSONALIZADA =====
        // Se o notificado é o próprio aluno dono do projeto...
        if ($notifiable->id === $alunoDoProjeto->id) {
            $mensagem = "Sua proposta '{$tituloProjeto}' recebeu o parecer '{$this->parecer}' do(a) {$this->avaliador}.";
        } 
        // Senão, asumimos que é o professor orientador...
        else {
            $mensagem = "A proposta '{$tituloProjeto}' do aluno(a) {$alunoDoProjeto->name} recebeu o parecer '{$this->parecer}' do(a) {$this->avaliador}.";
        }
        // ===============================================

        if ($this->parecer === 'Recusado') {
            $mensagem .= " Clique para ver os detalhes.";
        }

        return [
            'projeto_id' => $this->projeto->id,
            'titulo_projeto' => $tituloProjeto,
            'mensagem' => $mensagem,
            'url' => route('projetos.show', $this->projeto->id),
        ];
    }
}