<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResultadoRequest;
use App\Models\RejeicaoResultado;
use App\Models\Anexo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ResultadoEnviado;
use App\Notifications\ResultadoAvaliado;
use App\Notifications\ResultadoCadastradoPeloAluno;


class ResultadoController extends Controller
{
    /**
     * Mostra o formulário para criar um novo relatório de resultados.
     */
    public function create(Projeto $projeto)
    {

        $this->authorize('create', [Resultado::class, $projeto]);

        // CORREÇÃO: Carrega as relações necessárias ANTES das verificações.
        $projeto->load(['users.curso', 'atividades']);

        if ($projeto->resultado) {
            return redirect()->route('resultados.edit', $projeto->resultado)->with('info', 'Este projeto já possui um relatório de resultados. Você pode editá-lo aqui.');
        }
        if ($projeto->etapa !== 'Proposta' || $projeto->status !== 'aprovado') {
            return redirect()->route('projetos.index')->with('error', 'Só é possível adicionar um relatório após a proposta ser aprovada.');
        }

        // CORREÇÃO: Cria as coleções esperadas pela view
        $professores = $projeto->users->filter(fn($user) => str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador'));
        $alunos = $projeto->users->where('role', 'aluno');
        $cargaHorariaTotal = $projeto->atividades->sum('carga_horaria'); 

        return view('resultados.create', compact('projeto', 'professores', 'alunos', 'cargaHorariaTotal'));
    }

    /**
     * Salva o novo relatório de resultados no banco de dados.
     */
   public function store(StoreResultadoRequest $request, Projeto $projeto)
    {
        if ($projeto->resultado) {
            return redirect()->route('resultados.edit', $projeto->resultado)
                ->with('error', 'Este projeto já possui um relatório de resultados. Em vez de criar um novo, edite o existente.');
        }

        $this->authorize('create', [Resultado::class, $projeto]);

        $data = $request->validated();
        $data['projeto_id'] = $projeto->id;

        $resultado = Resultado::create($data);

        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $arquivo) {
                $caminho = $arquivo->store('resultados/' . $resultado->id, 'public');
                
                Anexo::create([
                    'resultado_id' => $resultado->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'path' => $caminho,
                    'mime_type' => $arquivo->getMimeType(),
                ]);
            }
        }

        $projeto->etapa = 'Resultado';
        $projeto->save();

        // CORREÇÃO: Usa 'projeto.users' em vez de 'projeto.professores.user'
        $resultado->load('projeto.user', 'projeto.users');
        
        // Filtra a coleção 'users' para encontrar os professores/coordenadores
        $professores = $resultado->projeto->users->filter(fn($user) => str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador'));

        if ($professores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($professores, new \App\Notifications\ResultadoCadastradoPeloAluno($resultado));
        }

        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório salvo com sucesso! Agora você já pode enviar para avaliação.');
    }

    /**
     * Mostra os detalhes de um resultado.
     */
    public function show(Resultado $resultado, Request $request)
    {
        $this->authorize('view', $resultado);

        // Pega a direção da ordenação da URL (?sort=asc), o padrão é 'desc'
        $sortDirection = $request->query('sort', 'desc');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Carrega as relações necessárias, incluindo todos os logs do projeto
        $resultado->load(['projeto.user', 'rejeicoes.user', 'projeto.todosOsLogs.user']);

        // Ordena a coleção de logs
        $logs = $resultado->projeto->todosOsLogs;
        if ($sortDirection === 'asc') {
            $logs = $logs->sortBy('created_at');
        } else {
            $logs = $logs->sortByDesc('created_at');
        }
        
        // Prepara todos os dados que a view precisa
        $data = [
            'resultado' => $resultado,
            'projeto'   => $resultado->projeto,
            'logs' => $logs, // A variável de logs, agora definida e ordenada
            'sortDirection' => $sortDirection, // A direção da ordenação para o botão
        ];

        // Cria a resposta com os dados e os headers
        $response = response(view('resultados.show', $data));
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }

    /**
     * Mostra o formulário para editar um resultado.
     */
    public function edit(Resultado $resultado)
    {
        // Policy: Verifica se o usuário pode atualizar (editar) este resultado.
        $this->authorize('update', $resultado);

        // CORREÇÃO: Carrega as relações necessárias para a exibição na view (resolve o erro pluck() on null na edit.blade.php)
        $resultado->load(['projeto.users.curso', 'projeto.atividades']);

        // Cria as coleções esperadas pela view (professores e alunos)
        $professores = $resultado->projeto->users->filter(fn($user) => str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador'));
        $alunos = $resultado->projeto->users->where('role', 'aluno');
        
        // Calcula a carga horária total
        $cargaHorariaTotal = $resultado->projeto->atividades->sum('carga_horaria'); 

        // Passa as coleções filtradas para a view
        return view('resultados.edit', compact('resultado', 'professores', 'alunos', 'cargaHorariaTotal'));
    }

    /**
     * Atualiza um resultado existente no banco de dados.
     */
    public function update(StoreResultadoRequest $request, Resultado $resultado)
    {
        // 1. Autorização (seu código original, mantido)
        $this->authorize('update', $resultado);

        // 2. Pega os dados validados do formulário
        $validatedData = $request->validated();

        // 3. GERENCIAR EXCLUSÃO DE ANEXOS MARCADOS
        if ($request->has('anexos_a_deletar')) {
            // Encontra os anexos pelos IDs enviados no formulário
            $anexosParaDeletar = Anexo::whereIn('id', $request->anexos_a_deletar)->get();
            
            foreach ($anexosParaDeletar as $anexo) {
                // Apaga o arquivo físico do disco
                Storage::disk('public')->delete($anexo->path);
                // Deleta o registro do banco de dados
                $anexo->delete();
            }
        }

        // 4. GERENCIAR UPLOAD DE NOVOS ANEXOS
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $arquivo) {
                // Salva o novo arquivo na pasta correta
                $caminho = $arquivo->store('resultados/' . $resultado->id, 'public');
                // Cria o registro do novo anexo no banco
                Anexo::create([
                    'resultado_id' => $resultado->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'path' => $caminho,
                    'mime_type' => $arquivo->getMimeType(),
                ]);
            }
        }

        // 5. ATUALIZAR OS DADOS DO RELATÓRIO
        // Primeiro, atualiza os campos de texto que foram validados
        $resultado->update($validatedData);

        // 6. APLICAR A LÓGICA DE REENVIO (seu código original, mantido e otimizado)
        // Se o relatório estava reprovado, ele é resetado para uma nova avaliação.
        if ($resultado->status === 'reprovado') {
            $resultado->update([
                'status' => 'enviado',
                'aprovado_napex' => 'pendente',
                'parecer_napex' => null,
                'aprovado_coordenador' => 'pendente',
                'parecer_coordenador' => null,
            ]);
        }

        // 7. Redirecionar para a página de visualização com mensagem de sucesso
        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados atualizado com sucesso!');
    }

    /**
     * Submete o resultado para avaliação.
     */


    public function enviar(Resultado $resultado)
    {
  
        $this->authorize('sendForEvaluation', $resultado);

        $resultado->status = 'entregue';
        $resultado->aprovado_napex = 'pendente';
        $resultado->parecer_napex = null;
        $resultado->aprovado_coordenador = 'pendente';
        $resultado->parecer_coordenador = null;
        $resultado->save();

        $resultado->load('projeto.user');
        
        $projeto = $resultado->projeto;
        $avaliadores = $this->getEvaluationRecipients($projeto);

        if ($avaliadores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($avaliadores, new \App\Notifications\ResultadoEnviado($resultado));
        }

        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados enviado para avaliação!');
    }

        private function getEvaluationRecipients(Projeto $projeto)
    {
        $projeto->load('user.curso');
    
        $cursoId = $projeto->user->curso_id;
    
        if (!$cursoId) {
            return User::where('role', 'napex')->get();
        }
    
        $coordenador = User::where('role', 'coordenador')
            ->whereHas('cursosCoordenados', function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId);
            })->first();

        $napexUsers = User::where('role', 'napex')->get();

        $recipients = collect([]);
        if ($coordenador) {
            $recipients->push($coordenador);
        }
        
        $recipients = $recipients->merge($napexUsers)->unique('id');
    
        return $recipients;
    }

    /**
     * Retorna um resultado enviado de volta para o status de rascunho.
     */
    public function voltarParaRascunho(Resultado $resultado)
    {
        // Policy: Verifica se o usuário tem permissão para reverter para rascunho.
        $this->authorize('revertToDraft', $resultado);

        $resultado->status = 'editando';
        $resultado->save();

        return redirect()->route('resultados.edit', $resultado)->with('success', 'O relatório de resultados voltou para o modo de edição.');
    }

    /**
     * Salva ou atualiza o parecer de um avaliador (NAPEX ou Coordenador).
     */

    public function avaliar(Request $request, Resultado $resultado)
    {
        $this->authorize('evaluate', $resultado);

        $user = auth()->user();
        $role = $user->role;

        $resultado->load('projeto.user');
        $aluno = $resultado->projeto->user;

        if ($role === 'napex') {
            if ($request->aprovacao === 'sim') {
                $resultado->registrarLog('PARECER_NAPEX', 'Parecer do NAPEX no Relatório: Aprovado.');
            } else {
                $descricao = 'Relatório Recusado pelo NAPEX. Motivo: ' . ($request->parecer ?? 'Não especificado.');
                $resultado->registrarLog('RECUSA_NAPEX', $descricao);
            }
            $resultado->parecer_napex = $request->parecer;
            $resultado->aprovado_napex = $request->aprovacao;

            if ($aluno) {
                if ($request->aprovacao === 'sim') {
                    $aluno->notify(new \App\Notifications\ResultadoAvaliado($resultado, 'Aprovado', null, 'NAPEX'));
                } else {
                    $aluno->notify(new \App\Notifications\ResultadoAvaliado($resultado, 'Recusado', $request->parecer, 'NAPEX'));
                }
            }


        } elseif ($role === 'coordenador') {

            if ($request->aprovacao === 'sim') {
                $resultado->registrarLog('PARECER_COORDENADOR', 'Parecer do Coordenador no Relatório: Aprovado.');
            } else {
                $descricao = 'Relatório Recusado pelo Coordenador. Motivo: ' . ($request->parecer ?? 'Não especificado.');
                $resultado->registrarLog('RECUSA_COORDENADOR', $descricao);
            }
            $resultado->parecer_coordenador = $request->parecer;
            $resultado->aprovado_coordenador = $request->aprovacao;

            if ($aluno) {
                if ($request->aprovacao === 'sim') {
                    $aluno->notify(new \App\Notifications\ResultadoAvaliado($resultado, 'Aprovado', null, 'Coordenador'));
                } else {
                    $aluno->notify(new \App\Notifications\ResultadoAvaliado($resultado, 'Recusado', $request->parecer, 'Coordenador'));
                }
            }

        }

        $aprovadoNapex = $resultado->aprovado_napex;
        $aprovadoCoord = $resultado->aprovado_coordenador;

        if ($aprovadoNapex === 'nao' || $aprovadoCoord === 'nao') {
            $resultado->status = 'reprovado';
        
            if ($request->aprovacao === 'nao') {
                RejeicaoResultado::create([
                    'resultado_id' => $resultado->id,
                    'user_id' => $user->id,
                    'motivo' => $request->parecer,
                ]);
            }
        } elseif ($aprovadoNapex === 'sim' && $aprovadoCoord === 'sim') {
            $resultado->status = 'aprovado';
            $projeto = $resultado->projeto;
            $projeto->etapa = 'Concluído';
            $projeto->save();
            $resultado->registrarLog('PROJETO_CONCLUIDO', 'Projeto movido para a etapa "Concluído" após aprovação final do relatório.');
        }
        
        $resultado->save();

        return redirect()->route('projetos.show', $resultado->projeto_id)->with('success', 'Parecer salvo com sucesso!');
    }

    public function gerarPdf(Resultado $resultado)
    {

        $this->authorize('view', $resultado);

        // CORREÇÃO: Usa o relacionamento 'users' e remove a linha duplicada
        $resultado->load(['projeto.users.curso', 'anexos']);
        $pdf = Pdf::loadView('pdf.resultados-relatorio', compact('resultado'));
        

        $nomeArquivo = "relatorio_resultados_{$resultado->projeto->id}.pdf";
        

        return $pdf->download($nomeArquivo);
    }
}