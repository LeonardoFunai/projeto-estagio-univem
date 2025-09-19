<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PropostaEnviada;
use Illuminate\Support\Facades\Notification;
use App\Models\Projeto;
use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Atividade;
use App\Models\Cronograma;
use App\Models\Rejeicao; 
use App\Models\Curso;
use App\Http\Requests\StoreProjetoRequest;
use App\Http\Requests\UpdateProjetoRequest;
use App\Services\ProjectSearchService;
use Illuminate\Http\Request; 
use Illuminate\Database\QueryException; 
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Notifications\PropostaAvaliada;


class ProjetoController extends Controller
{
    /**
     * Exibe uma lista de projetos com base nos filtros aplicados e no papel (role) do usuário.
     * Permite paginação e ordenação.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request, ProjectSearchService $searchService)
    {
     
        $query = $searchService->buildQuery($request->all());

        
        $projetos = $query->paginate(10)->appends($request->query());

        $response = response(view('projetos.index', compact('projetos')));
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }
    
    /**
     * Exibe o formulário para criação de um novo projeto.
     * Apenas alunos podem criar projetos.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        $this->authorize('create', Projeto::class);

        $professores = User::where('role', 'professor')->orderBy('name')->get();
        $cursos = Curso::orderBy('nome')->get(); 

        return view('projetos.create', compact('professores', 'cursos'));
    }

    /**
     * Armazena um novo projeto no banco de dados.
     * Utiliza StoreProjetoRequest para validação dos dados.
     *
     * @param  \App\Http\Requests\StoreProjetoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreProjetoRequest $request)
    {

        $this->authorize('create', Projeto::class);


        $data = $request->validated(); 
        $data['status'] = 'editando'; 


        if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
            $file = $request->file('arquivo');
            $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->extension();
            $file->move(public_path('arquivos_projetos'), $fileName); 
            $data['arquivo'] = 'arquivos_projetos/' . $fileName; 
        }


        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $inicio = date('d/m/Y', strtotime($request->input('data_inicio')));
            $fim = date('d/m/Y', strtotime($request->input('data_fim')));
            $data['periodo_realizacao'] = "$inicio a $fim";
        }

        $data['user_id'] = auth()->id(); 
        
        $projeto = Projeto::create($data); 

     
        if ($request->has('alunos')) {
            foreach ($request->alunos as $alunoData) { 
                
                if (!empty($alunoData['nome']) && !empty($alunoData['ra'])) { 
                    $projeto->alunos()->create($alunoData);
                }
            }
        }

        
        if ($request->has('professores')) {
            $professorIds = []; 

            foreach ($request->professores as $professorData) {
                
                if (empty($professorData['id'])) continue; 

                if (in_array($professorData['id'], $professorIds)) {
                    return redirect()->back()
                        ->withInput() 
                        ->with('error', 'Você tentou adicionar o mesmo professor mais de uma vez.');
                }
                $professorIds[] = $professorData['id'];

                $userProfessor = User::find($professorData['id']); 
                if ($userProfessor) {
                 
                    $projeto->professores()->create([
                        'nome' => $userProfessor->name,
                        'email' => $userProfessor->email,
                        'area' => $professorData['area'] ?? null, 
                        'user_id' => $userProfessor->id, 
                    ]);
                }
            }
        }

        if ($request->has('atividades')) {
            foreach ($request->atividades as $atividadeData) {
                 if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) { // Exemplo
                    $projeto->atividades()->create($atividadeData);
                 }
            }
        }

        if ($request->has('cronograma') && is_array($request->cronograma)) {
            foreach ($request->cronograma as $itemDataCronograma) {


                if (!empty($itemDataCronograma['atividade']) &&
                    !empty($itemDataCronograma['mes_inicio']) &&
                    !empty($itemDataCronograma['mes_fim'])) {

                    $projeto->cronogramas()->create([
                        'atividade'  => $itemDataCronograma['atividade'],
                        'mes_inicio' => $itemDataCronograma['mes_inicio'],
                        'mes_fim'    => $itemDataCronograma['mes_fim'],
                     
                    ]);
                }
            }
        }

        return redirect()->route('projetos.index')->with('success', 'Projeto salvo com sucesso!');
    }

    /**
     * Exibe os detalhes de um projeto específico.
     * Carrega todas as relações necessárias (eager loading).
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show($id, Request $request)
    {
        $sortDirection = $request->query('sort', 'desc');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'rejeicoes', 'user', 'todosOsLogs.user', 'resultado'])
                        ->findOrFail($id);

        $user = auth()->user();
        if (in_array($user->role, ['napex', 'coordenador'])) {
            if (!in_array($projeto->status, ['entregue', 'aprovado'])) {
                abort(403, 'Acesso não autorizado para este status de proposta.');
            }
        }
        $this->authorize('view', $projeto);

        $logs = $projeto->todosOsLogs;
        if ($sortDirection === 'asc') {
            $logs = $logs->sortBy('created_at');
        } else {
            $logs = $logs->sortByDesc('created_at');
        }

        $data = [
            'projeto' => $projeto,
            'logs' => $logs,
            'sortDirection' => $sortDirection,
        ];

   
        $response = response(view('projetos.show', $data));
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }

    /**
     * Exibe o formulário para edição de um projeto existente.
     * Contém lógica de permissão para edição.
     *
     * @param  string  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(string $id)
    {
        $projeto = Projeto::with(['professores', 'alunos', 'atividades', 'cronogramas'])->findOrFail($id);
        $this->authorize('update', $projeto);

        $professores = User::where('role', 'professor')->orderBy('name')->get();
        
    
        $cursos = Curso::orderBy('nome')->get();

        
        return view('projetos.edit', compact('projeto', 'professores', 'cursos'));
    }

    /**
     * Atualiza um projeto existente no banco de dados.
     * Utiliza UpdateProjetoRequest para validação e a ProjetoPolicy para autorização.
     *
     * @param  \App\Http\Requests\UpdateProjetoRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProjetoRequest $request, $id)
    {
        $projeto = Projeto::with('professores')->findOrFail($id);

        $this->authorize('update', $projeto);
        
        $data = $request->validated();
        
        $projeto->update($data);

        $projeto->alunos()->delete();
        if ($request->has('alunos')) {
            foreach ($request->alunos as $alunoData) {
                if (!empty($alunoData['nome']) && !empty($alunoData['ra'])) {
                    $projeto->alunos()->create($alunoData);
                }
            }
        }
    
        $projeto->professores()->delete();
        if ($request->has('professores')) {
            $professorIds = [];
            foreach ($request->professores as $professorData) {
                if (empty($professorData['id'])) continue;

                if (in_array($professorData['id'], $professorIds)) {
                    return redirect()->back()->withInput()->with('error', 'Você tentou adicionar o mesmo professor mais de uma vez.');
                }
                $professorIds[] = $professorData['id'];
    
                $userProfessor = User::find($professorData['id']);
                if ($userProfessor) {
                    $projeto->professores()->create([
                        'user_id' => $userProfessor->id,
                        'nome' => $userProfessor->name,
                        'email' => $userProfessor->email,
                        'area' => $professorData['area'] ?? null,
                    ]);
                }
            }
        }

        $projeto->atividades()->delete();
        if ($request->has('atividades')) {
            foreach ($request->atividades as $atividadeData) {
                if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                    $projeto->atividades()->create($atividadeData);
                }
            }
        }
    
        $projeto->cronogramas()->delete();
        if ($request->has('cronograma') && is_array($request->cronograma)) {
            foreach ($request->cronograma as $cronogramaItem) {
               
                $projeto->cronogramas()->create($cronogramaItem);
            }
        }
    
        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Projeto atualizado com sucesso!');
    }
        
    /**
     * Processa a avaliação de um projeto pelo NAPEx.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function avaliarNapex(Request $request, $id)
    {
        $projeto = Projeto::with('user')->findOrFail($id);
        $this->authorize('approveByNapex', $projeto);

        $validatedData = $request->validate([
            'aprovado_napex' => 'required|in:sim,nao',
            'motivo_napex' => 'nullable|string|required_if:aprovado_napex,nao|max:2000',
            'numero_projeto' => 'nullable|string|max:255',
        ]);

        $projeto->fill($validatedData);
        $projeto->data_parecer_napex = now();
        if ($projeto->aprovado_napex === 'sim' && isset($validatedData['numero_projeto'])) {
            $projeto->numero_projeto = $validatedData['numero_projeto'];
        }
        
        // Salva a avaliação do NAPEX
        $projeto->save();

        // Notifica o aluno sobre ESTA avaliação específica
        if ($projeto->user) {
            if ($projeto->aprovado_napex === 'sim') {
                $projeto->user->notify(new PropostaAvaliada($projeto, 'Aprovado', null, 'NAPEX'));
            } else {
                $projeto->user->notify(new PropostaAvaliada($projeto, 'Recusado', $projeto->motivo_napex, 'NAPEX'));
            }
        }

        // AGORA, verificamos o estado geral do projeto
        if ($projeto->aprovado_napex === 'nao') {
            $this->registrarRejeicao($projeto, $projeto->motivo_napex, 'napex');
            $projeto->status = 'editando';
            $projeto->save();
            return redirect()->route('projetos.index')->with('success', 'Projeto NÃO APROVADO. O aluno foi notificado.');
        }
        
        // Se ambos aprovaram, muda o status geral e envia a notificação final
        if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
            $projeto->status = 'aprovado';
            $projeto->save();
            // Opcional: Enviar a notificação de "Próxima Etapa" que criamos antes
            // $projeto->user->notify(new PropostaAprovadaFinal($projeto));
        }

        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Parecer do NAPEx salvo com sucesso.');
    }

    /**
     * Processa a avaliação de um projeto pelo Coordenador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
  public function avaliarCoordenador(Request $request, $id)
{
    $projeto = Projeto::with('user')->findOrFail($id);
    $this->authorize('approveByCoordinator', $projeto);

    $validatedData = $request->validate([
        'aprovado_coordenador' => 'required|in:sim,nao',
        'motivo_coordenador' => 'nullable|string|required_if:aprovado_coordenador,nao|max:2000',
    ]);

    $projeto->fill($validatedData);
    $projeto->data_parecer_coordenador = now();
    
    // Salva a avaliação do Coordenador
    $projeto->save();

    // Notifica o aluno sobre ESTA avaliação específica
    if ($projeto->user) {
        if ($projeto->aprovado_coordenador === 'sim') {
            $projeto->user->notify(new PropostaAvaliada($projeto, 'Aprovado', null, 'Coordenador'));
        } else {
            $projeto->user->notify(new PropostaAvaliada($projeto, 'Recusado', $projeto->motivo_coordenador, 'Coordenador'));
        }
    }

    // AGORA, verificamos o estado geral do projeto
    if ($projeto->aprovado_coordenador === 'nao') {
        $this->registrarRejeicao($projeto, $projeto->motivo_coordenador, 'coordenador');
        $projeto->status = 'editando';
        $projeto->save();
        return redirect()->route('projetos.index')->with('success', 'Projeto NÃO APROVADO. O aluno foi notificado.');
    }
    
    // Se ambos aprovaram, muda o status geral e envia a notificação final
    if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
        $projeto->status = 'aprovado';
        $projeto->save();
        // Opcional: Enviar a notificação de "Próxima Etapa"
        // $projeto->user->notify(new PropostaAprovadaFinal($projeto));
    }

    return redirect()->route('projetos.show', $projeto->id)->with('success', 'Parecer do Coordenador salvo com sucesso.');
}

    /**
     * Exclui um projeto.
     * A autorização é tratada pela ProjetoPolicy.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $projeto = Projeto::findOrFail($id);



        $this->authorize('delete', $projeto);

        try {
            // Lógica para deletar arquivos associados
            if ($projeto->arquivo && file_exists(public_path($projeto->arquivo))) {
                unlink(public_path($projeto->arquivo));
            }

            $projeto->delete(); 

            return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');

        } catch (\Exception $e) {

            return redirect()->route('projetos.index')->with('error', 'Erro ao excluir o projeto.');
        }
    }

    /**
     * Marca um projeto como 'entregue' pelo aluno.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enviarProjeto($id)
    {

        $projeto = \App\Models\Projeto::findOrFail($id);
        

        $this->authorize('submit', $projeto);


        $projeto->status = 'entregue';
        $projeto->data_entrega = now();
        $projeto->aprovado_napex = 'pendente';
        $projeto->motivo_napex = null;
        $projeto->data_parecer_napex = null;
        $projeto->aprovado_coordenador = 'pendente';
        $projeto->motivo_coordenador = null;
        $projeto->data_parecer_coordenador = null;
        $destinatarios = collect();


        $professorIds = \App\Models\Professor::where('projeto_id', $projeto->id)->pluck('user_id')->filter();
        if ($professorIds->isNotEmpty()) {
            $destinatarios = $destinatarios->merge(\App\Models\User::whereIn('id', $professorIds)->get());
        }


        $avaliadores = \App\Models\User::whereIn('role', ['napex', 'coordenador'])->get();
        if ($avaliadores->isNotEmpty()) {
            $destinatarios = $destinatarios->merge($avaliadores);
        }
        
        if ($destinatarios->isNotEmpty()) {
            $destinatariosUnicos = $destinatarios->unique('id');
            
            \Illuminate\Support\Facades\Notification::send($destinatariosUnicos, new \App\Notifications\PropostaEnviada($projeto));
        }
        

        $projeto->save();
        
        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Projeto enviado para avaliação com sucesso!');
    }

    /**
     * Permite que um projeto 'entregue' (mas ainda não avaliado/aprovado) volte para o status 'editando'.
     * Pode ser útil se o aluno/professor perceber um erro logo após o envio.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
/**
     * Permite que um projeto 'entregue' (mas ainda não avaliado) volte para 'editando'.
     * A autorização é tratada pela ProjetoPolicy@revertToEditing.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voltarParaEdicao($id)
    {

        $projeto = Projeto::findOrFail($id);

        $this->authorize('revertToEditing', $projeto);
    
        $projeto->status = 'editando';
        $projeto->data_entrega = null; 
        $projeto->save();
    
        return redirect()->route('projetos.edit', $projeto)->with('success', 'A proposta voltou para o modo de edição.');  
    }
    
    /**
     * Registra uma rejeição para um projeto.
     *
     * @param  \App\Models\Projeto  $projeto
     * @param  string|null  $motivo
     * @param  string  $autor ('napex' ou 'coordenador')
     * @return void
     */
    private function registrarRejeicao($projeto, $motivo, $autor)
    {
        Rejeicao::create([
            'projeto_id' => $projeto->id,
            'motivo' => $motivo ?? 'Motivo não especificado.', 
            'data_rejeicao' => now(),
            'autor' => $autor, 
        ]);
    }
    
    /**
     * Exporta uma lista filtrada de projetos para um arquivo PDF.
     * A autorização é tratada pela ProjetoPolicy@exportGeneralPdf.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportarPdf(Request $request, ProjectSearchService $searchService)
    {
        $this->authorize('exportGeneralPdf', Projeto::class);

        $query = $searchService->buildQuery($request->all());
        $projetos = $query->get();

        $filtros = $request->except(['_token']);
        $usuarioLogado = auth()->user()->name;

        $pdf = Pdf::loadView('pdf.projetos-relatorio', [
            'projetos' => $projetos,
            'filtros' => $filtros,
            'usuarioLogado' => $usuarioLogado,
            'dataGeracao' => now()->format('d/m/Y H:i:s')
        ]);
        
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('relatorio_projetos_extensionistas.pdf');
    }

    /**
     * Gera o PDF da proposta individual de um projeto.
     *
     * @param  string  $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gerarPdf($id)
    {

        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'user', 'rejeicoes'])->findOrFail($id);



        $this->authorize('view', $projeto);


        $pdf = Pdf::loadView('projetos.pdf', compact('projeto'));

        $nomeArquivo = "proposta_extensionista_{$projeto->id}.pdf";
        $nomeArquivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeArquivo);

        return $pdf->download($nomeArquivo);
    }

    public function exportarLogPdf(Projeto $projeto)
    {

        $this->authorize('view', $projeto);

 
        $projeto->load('todosOsLogs.user');


        $data = [
            'projeto' => $projeto,
            'logs' => $projeto->todosOsLogs
        ];


        $pdf = Pdf::loadView('pdf.logs-historico', $data);

        return $pdf->download('historico-projeto-' . $projeto->id . '.pdf');
    }
}