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

       
        $projeto->load(['users.curso', 'atividades']);

        if ($projeto->resultado) {
            return redirect()->route('resultados.edit', $projeto->resultado)->with('info', 'Este projeto já possui um relatório de resultados. Você pode editá-lo aqui.');
        }
        if ($projeto->etapa !== 'Proposta' || $projeto->status !== 'aprovado') {
            return redirect()->route('projetos.index')->with('error', 'Só é possível adicionar um relatório após a proposta ser aprovada.');
        }

      
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

        if ($request->has('anexos')) {
            foreach ($request->anexos as $anexoData) {
                if (isset($anexoData['arquivo']) && $anexoData['arquivo']->isValid()) {
                    $file = $anexoData['arquivo'];
                    $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->extension();
                    $filePath = $file->storeAs('anexos_resultados', $fileName, 'public');

                    
                    $resultado->anexos()->create([
                        'descricao' => $anexoData['descricao'],
                        'path' => $filePath,
                        'nome_original' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
        }

        $projeto->etapa = 'Resultado';
        $projeto->save();

        $resultado->load('projeto.user', 'projeto.users');

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
        $resultado->load('projeto.users');

        $sortDirection = $request->query('sort', 'desc');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        $resultado->load([
                'projeto.user',
                'projeto.alunos',          
                'projeto.professores',     
                'projeto.atividades', 
                'rejeicoes.user',
                'projeto.todosOsLogs.user'
            ]);

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
        $this->authorize('update', $resultado);

        $validatedData = $request->validated();

        if ($request->has('anexos_a_deletar')) {
            $anexosParaDeletar = Anexo::whereIn('id', $request->anexos_a_deletar)
                                    ->where('resultado_id', $resultado->id) 
                                    ->get();
            
            foreach ($anexosParaDeletar as $anexo) {
                Storage::disk('public')->delete($anexo->path);
                $anexo->delete();
            }
        }

        if ($request->has('anexos')) {
            foreach ($request->anexos as $anexoData) {
                if (isset($anexoData['arquivo']) && $anexoData['arquivo']->isValid()) {
                    $file = $anexoData['arquivo'];
                    $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->extension();
                    $filePath = $file->storeAs('anexos_resultados', $fileName, 'public');

                    $resultado->anexos()->create([
                        'descricao' => $anexoData['descricao'],
                        'path' => $filePath,
                        'nome_original' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }
        }

        unset($validatedData['anexos']); 
        $resultado->update($validatedData);

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

        $resultado->load([
            'projeto.professores', 
            'projeto.alunos.curso', 
            'projeto.atividades', 
            'anexos'
        ]);

        $projeto = $resultado->projeto;
        $professores = $projeto->professores;
        $alunos = $projeto->alunos;
        $cargaHorariaTotal = $projeto->atividades->sum('carga_horaria');

        $pdf = Pdf::loadView('pdf.resultados-relatorio', compact(
            'resultado', 
            'projeto', 
            'professores', 
            'alunos', 
            'cargaHorariaTotal'
        ));
        
        $numero = $projeto->numero_projeto ?? "ID-{$projeto->id}";
        $nomeArquivo = "{$numero}-relatorio-resultados.pdf";
        
        return $pdf->download($nomeArquivo);
    }
}