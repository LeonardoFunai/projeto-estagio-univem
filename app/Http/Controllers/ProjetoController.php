<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PropostaEnviada;
use Illuminate\Support\Facades\Notification;
use App\Models\Projeto;
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
use App\Notifications\ProfessorVinculadoAProjeto;


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

        // Carrega o usuário logado e seu relacionamento de curso para exibir na view.
        $alunoLogado = auth()->user()->load('curso');

        // Busca apenas os outros alunos (excluindo o que está logado) para o campo de busca.
        $outrosAlunos = User::where('role', 'aluno')
                            ->where('id', '!=', $alunoLogado->id)
                            ->orderBy('name')
                            ->get();

        // Busca os professores e coordenadores para o campo de busca.
        $professores = User::where('role', 'like', 'professor%')
                        ->orWhere('role', 'like', 'coordenador%')
                        ->orderBy('name')
                        ->get();

        // Envia as variáveis para a view.
        return view('projetos.create', compact('alunoLogado', 'outrosAlunos', 'professores'));
    }
    

    public function searchUsers(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        if (empty($search) || empty($role)) {
            return response()->json([]);
        }

        $query = User::query()->orderBy('name');

        if ($role === 'aluno') {
            // CORREÇÃO: Adicionamos with('curso') para carregar os dados do curso
            $query->with('curso')
                ->where('role', 'aluno')
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('ra', 'like', "%{$search}%")
                        ->orWhereHas('curso', function ($cq) use ($search) {
                            $cq->where('nome', 'like', "%{$search}%");
                        });
                });
        } elseif ($role === 'professor') {
            $query->where(function ($q) {
                $q->where('role', 'like', 'professor%')
                ->orWhere('role', 'like', 'coordenador%');
            })
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(10)->get();

        return response()->json($users);
    }

    /**
     * Armazena um novo projeto no banco de dados.c
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

        // A lógica de arquivo e período permanece a mesma
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

        // Define o criador do projeto como o usuário logado
        $data['user_id'] = auth()->id();
        
        // Cria o projeto com os dados principais
        $projeto = Projeto::create($data);
        $alunoLogadoId = auth()->id();
        $outrosAlunosIds = $request->input('alunos', []);
        $professoresIds = $request->input('professores', []);
        
        // Une o ID do aluno logado com os outros alunos e professores selecionados
        $todosOsParticipantesIds = array_unique(array_merge([$alunoLogadoId], $outrosAlunosIds, $professoresIds));

        // O método sync() anexa todos os participantes ao projeto de uma só vez
        if (!empty($todosOsParticipantesIds)) {
            $projeto->users()->sync($todosOsParticipantesIds);
        }

        // Notifica apenas os professores
        if (!empty($professoresIds)) {
            $professoresParaNotificar = User::findMany($professoresIds);
            if ($professoresParaNotificar->isNotEmpty()) {
                Notification::send($professoresParaNotificar, new \App\Notifications\ProfessorVinculadoAProjeto($projeto));
            }
        }


        if ($request->has('atividades')) {
            foreach ($request->atividades as $atividadeData) {
                if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                    $projeto->atividades()->create($atividadeData);
                }
            }
        }

        if ($request->has('cronograma') && is_array($request->cronograma)) {
            foreach ($request->cronograma as $itemDataCronograma) {
                if (!empty($itemDataCronograma['atividade']) && !empty($itemDataCronograma['mes_inicio']) && !empty($itemDataCronograma['mes_fim'])) {
                    $projeto->cronogramas()->create($itemDataCronograma);
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

        // --- CORREÇÃO APLICADA AQUI ---
        // Trocamos 'alunos' e 'professores' pelo novo relacionamento 'users'.
        $projeto = Projeto::with([
            'users', // Carrega todos os participantes (alunos e professores)
            'atividades', 
            'cronogramas', 
            'rejeicoes', 
            'user', // Carrega o criador do projeto
            'todosOsLogs.user', 
            'resultado'
        ])->findOrFail($id);

        $this->authorize('view', $projeto);

        $logs = $projeto->todosOsLogs;
        if ($sortDirection === 'asc') {
            $logs = $logs->sortBy('created_at');
        } else {
            $logs = $logs->sortByDesc('created_at');
        }

        // Separa os participantes em alunos e professores para a view
        $alunos = $projeto->users->where('role', 'aluno');
        $professores = $projeto->users->filter(function ($user) {
            return str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador');
        });

        $data = [
            'projeto' => $projeto,
            'logs' => $logs,
            'sortDirection' => $sortDirection,
            'alunos' => $alunos,
            'professores' => $professores,
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
    public function edit(Projeto $projeto)
    {
        $this->authorize('update', $projeto);


        $projeto->load('users', 'atividades', 'cronogramas');

        $alunos = User::where('role', 'aluno')->orderBy('name')->get();


        $professores = User::where('role', 'like', 'professor%')
                        ->orWhere('role', 'like', 'coordenador%')
                        ->orderBy('name')
                        ->get();

        $cursos = Curso::orderBy('nome')->get();

        $participantesIds = $projeto->users()->pluck('id')->toArray();

        return view('projetos.edit', compact('projeto', 'alunos', 'professores', 'cursos', 'participantesIds'));
    }

    /**
     * Atualiza um projeto existente no banco de dados.
     * Utiliza UpdateProjetoRequest para validação e a ProjetoPolicy para autorização.
     *
     * @param  \App\Http\Requests\UpdateProjetoRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProjetoRequest $request, Projeto $projeto)
    {
        $this->authorize('update', $projeto);

        $data = $request->validated();

        if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
            if ($projeto->arquivo && file_exists(public_path($projeto->arquivo))) {
                unlink(public_path($projeto->arquivo));
            }
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

        $projeto->update($data);

        $alunosIds = $request->input('alunos', []);
        $professoresIds = $request->input('professores', []);
        
        $allUserIds = array_unique(array_merge($alunosIds, $professoresIds));
        $projeto->users()->sync($allUserIds);

        // Opcional: Lógica de notificação para professores recém-adicionados
        if (!empty($professoresIds)) {
            $professoresParaNotificar = User::findMany($professoresIds);
            if ($professoresParaNotificar->isNotEmpty()) {
                Notification::send($professoresParaNotificar, new \App\Notifications\ProfessorVinculadoAProjeto($projeto));
            }
        }

        // A lógica para atualizar atividades e cronogramas deve ser mais complexa,
        // usando sync() ou delete/create para evitar duplicatas.
        // Por enquanto, manteremos a simplicidade de adicionar novos.
        if ($request->has('atividades')) {
            $projeto->atividades()->delete(); // Apaga os antigos para adicionar os novos
            foreach ($request->atividades as $atividadeData) {
                if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                    $projeto->atividades()->create($atividadeData);
                }
            }
        }

        if ($request->has('cronograma')) {
            $projeto->cronogramas()->delete(); // Apaga os antigos para adicionar os novos
            foreach ($request->cronograma as $itemDataCronograma) {
                if (!empty($itemDataCronograma['atividade']) && !empty($itemDataCronograma['mes_inicio']) && !empty($itemDataCronograma['mes_fim'])) {
                    $projeto->cronogramas()->create($itemDataCronograma);
                }
            }
        }

        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
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
        $projeto = Projeto::with('user', 'professores.user')->findOrFail($id);
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
        
        $projeto->save();

        $aluno = $projeto->user;
        $professores = $projeto->professores->map(fn($p) => $p->user)->filter();
        $destinatarios = collect([$aluno])->merge($professores)->filter()->unique('id');

        if ($destinatarios->isNotEmpty()) {
            if ($projeto->aprovado_napex === 'sim') {
                \Illuminate\Support\Facades\Notification::send($destinatarios, new \App\Notifications\PropostaAvaliada($projeto, 'Aprovado', null, 'NAPEX'));
            } else {
                \Illuminate\Support\Facades\Notification::send($destinatarios, new \App\Notifications\PropostaAvaliada($projeto, 'Recusado', $projeto->motivo_napex, 'NAPEX'));
            }
        }

        if ($projeto->aprovado_napex === 'nao') {
            $this->registrarRejeicao($projeto, $projeto->motivo_napex, 'napex');
            $projeto->status = 'editando';
            $projeto->save();
            return redirect()->route('projetos.index')->with('success', 'Projeto NÃO APROVADO. O aluno e professor foram notificados.');
        }
        
        if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
            $projeto->status = 'aprovado';
            $projeto->save();
        }

        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Parecer do NAPEx salvo com sucesso.');
    }
    
    public function avaliarCoordenador(Request $request, $id)
    {
        $projeto = Projeto::with('user', 'professores.user')->findOrFail($id);
        $this->authorize('approveByCoordinator', $projeto);

        $validatedData = $request->validate([
            'aprovado_coordenador' => 'required|in:sim,nao',
            'motivo_coordenador' => 'nullable|string|required_if:aprovado_coordenador,nao|max:2000',
        ]);

        $projeto->fill($validatedData);
        $projeto->data_parecer_coordenador = now();
        
        $projeto->save();

        $aluno = $projeto->user;
        $professores = $projeto->professores->map(fn($p) => $p->user)->filter();
        $destinatarios = collect([$aluno])->merge($professores)->filter()->unique('id');

        if ($destinatarios->isNotEmpty()) {
            if ($projeto->aprovado_coordenador === 'sim') {
                \Illuminate\Support\Facades\Notification::send($destinatarios, new \App\Notifications\PropostaAvaliada($projeto, 'Aprovado', null, 'Coordenador'));
            } else {
                \Illuminate\Support\Facades\Notification::send($destinatarios, new \App\Notifications\PropostaAvaliada($projeto, 'Recusado', $projeto->motivo_coordenador, 'Coordenador'));
            }
        }

        if ($projeto->aprovado_coordenador === 'nao') {
            $this->registrarRejeicao($projeto, $projeto->motivo_coordenador, 'coordenador');
            $projeto->status = 'editando';
            $projeto->save();
            return redirect()->route('projetos.index')->with('success', 'Projeto NÃO APROVADO. O aluno e professor foram notificados.');
        }
        
        if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
            $projeto->status = 'aprovado';
            $projeto->save();
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
        $projeto = \App\Models\Projeto::with('user', 'professores.user')->findOrFail($id);
        
        $this->authorize('submit', $projeto);

        $projeto->status = 'entregue';
        $projeto->data_entrega = now();
        $projeto->aprovado_napex = 'pendente';
        $projeto->motivo_napex = null;
        $projeto->data_parecer_napex = null;
        $projeto->aprovado_coordenador = 'pendente';
        $projeto->motivo_coordenador = null;
        $projeto->data_parecer_coordenador = null;
        
        $projeto->save();

        $professores = $projeto->professores->map(function ($professor) {
            return $professor->user;
        })->filter();

        if ($professores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($professores, new \App\Notifications\ProjetoSubmetidoPeloAluno($projeto));
        }
        
        $avaliadores = \App\Models\User::whereIn('role', ['napex', 'coordenador'])->get();
        if ($avaliadores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($avaliadores, new \App\Notifications\PropostaEnviada($projeto));
        }
        
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
        $dataGeracao = now(); 

        $pdf = Pdf::loadView('pdf.projetos-relatorio', [
            'projetos' => $projetos,
            'filtros' => $filtros,
            'usuarioLogado' => $usuarioLogado,
            'dataGeracao' => $dataGeracao->format('d/m/Y H:i:s')
        ]);
        
        $pdf->setPaper('a4', 'portrait');

        $nomeArquivo = 'Relatorio_Projetos_' . $dataGeracao->format('Y-m-d_His') . '.pdf';
        
        return $pdf->download($nomeArquivo);
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
        
       
        return $pdf->download($projeto->id . '-proposta.pdf');
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