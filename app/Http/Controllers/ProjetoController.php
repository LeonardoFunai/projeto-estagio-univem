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
use Illuminate\Support\Facades\Auth;
use App\Models\ProjetoLog;
use App\Models\ProjetoInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        // 1. Define os filtros permitidos, incluindo o novo 'curso_id'
        $filters = $request->only(['search', 'status', 'curso_id']);

        // 2. Constrói a query com os filtros e já otimiza o carregamento do curso do usuário
        $query = $searchService->buildQuery($filters);

        // 3. Executa a paginação
        $projetos = $query->paginate(10)->appends($request->query());

        // 4. Busca a lista de cursos para popular o dropdown de filtro na tela
        $cursos = Curso::orderBy('nome')->get();

        // 5. Envia os projetos e a lista de cursos para a view
        $response = response(view('projetos.index', compact('projetos', 'cursos')));

        // 6. Mantém seus headers de controle de cache
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

        // A variável correta é '$alunoLogado'
        $alunoLogado = auth()->user();

        // Carrega o curso do aluno para evitar consultas extras
        $alunoLogado->load('curso');

        // Busca apenas os outros alunos (excluindo o que está logado) do mesmo curso
        $alunos = User::where('role', 'aluno')
                    // CORREÇÃO: Usar a variável '$alunoLogado'
                    ->where('curso_id', $alunoLogado->curso_id)
                    // CORREÇÃO: Usar a variável '$alunoLogado'
                    ->where('id', '!=', $alunoLogado->id)
                    ->orderBy('name')
                    ->get();

        // Busca os professores e coordenadores para o campo de busca
        $professores = User::where('role', 'like', 'professor%')
                        ->orWhere('role', 'like', 'coordenador%')
                        ->orderBy('name')
                        ->get();

        return view('projetos.create', compact('alunoLogado', 'alunos', 'professores'));
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $usuarioLogado = auth()->user();

        if (empty($search) || empty($role)) {
            return response()->json([]);
        }

        $query = User::query()->orderBy('name');

        if ($role === 'aluno') {
            $query->with('curso')
                ->where('role', 'aluno')
                // 1. GARANTE QUE OS ALUNOS BUSCADOS SÃO DO MESMO CURSO DO USUÁRIO LOGADO
                ->where('curso_id', $usuarioLogado->curso_id)
                // 2. GARANTE QUE O PRÓPRIO USUÁRIO NÃO APAREÇA NA LISTA
                ->where('id', '!=', $usuarioLogado->id)
                // 3. BUSCA APENAS POR NOME OU RA, JÁ QUE O CURSO JÁ ESTÁ FILTRADO
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ra', 'like', "%{$search}%");
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

        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Cria a instância do projeto com os dados validados
            $projeto = new Projeto($validatedData);
            $projeto->user_id = auth()->id();
            $projeto->status = 'editando';
            $projeto->etapa = 'Proposta';

            // Lógica para arquivo (se existir no seu formulário)
            if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
                $file = $request->file('arquivo');
                $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->extension();
                $filePath = $file->storeAs('arquivos_projetos', $fileName, 'public');
                $projeto->arquivo = $filePath;
            }
            
            // Salva o projeto para obter um ID
            $projeto->save();

            // 2. Anexa o criador (aluno logado) como o primeiro participante
            $projeto->users()->attach(auth()->id());

            // 3. Salva as atividades e cronogramas
            if (isset($validatedData['atividades'])) {
                foreach ($validatedData['atividades'] as $atividadeData) {
                    $projeto->atividades()->create($atividadeData);
                }
            }
            if (isset($validatedData['cronograma'])) {
                foreach ($validatedData['cronograma'] as $cronogramaData) {
                    $projeto->cronogramas()->create($cronogramaData);
                }
            }

            // 4. Processa e cria os convites
            if ($request->has('invitations')) {
                foreach ($request->input('invitations') as $invitationData) {
                    // Garante que os dados do convite não estão vazios
                    if (!empty($invitationData['email']) && !empty($invitationData['role'])) {
                        
                        // Evita que o criador se auto-convide
                        if ($invitationData['email'] === auth()->user()->email) {
                            continue;
                        }
                        
                        ProjetoInvitation::create([
                            'projeto_id' => $projeto->id,
                            'user_id'    => auth()->id(), // ID de quem convidou
                            'email'      => $invitationData['email'],
                            'role'       => $invitationData['role'],
                            'token'      => Str::uuid(),
                            'status'     => 'pendente',
                        ]);
                    }
                }
            }

            DB::commit();

            // 5. Redireciona para a página de edição para o próximo passo
            return redirect()->route('projetos.show', $projeto)->with('success', 'Proposta criada! Os convites foram enviados para os participantes.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Retorna para o formulário com os dados preenchidos e uma mensagem de erro
            return back()->withInput()->with('error', 'Ocorreu um erro ao salvar o projeto. Por favor, tente novamente.');
        }
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
        $projeto = Projeto::with([
            'users', 
            'atividades', 
            'cronogramas', 
            'rejeicoes', 
            'user',
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
    public function edit($id)
    {
        $projeto = Projeto::with([
            'users', 
            'invitations.inviter', 
            'atividades', 
            'cronogramas'
        ])->findOrFail($id);

        $this->authorize('update', $projeto);

        $orientadores = $projeto->users->filter(fn($user) => str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador'));
        $alunos = $projeto->users->filter(fn($user) => $user->role === 'aluno');
        $convitesPendentes = $projeto->invitations->where('status', 'pendente');

        return view('projetos.edit', compact(
            'projeto',
            'orientadores',
            'alunos',
            'convitesPendentes'
        ));
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
        // 1. Busca o projeto e autoriza a ação
        $projeto = Projeto::findOrFail($id);
        $this->authorize('update', $projeto);

        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // 2. Atualiza os dados principais do projeto (título, período, etc.)
            $projeto->update($validatedData);

            // 3. Processa e cria os NOVOS convites que foram adicionados na tela de edição
            if ($request->has('invitations')) {
                foreach ($request->input('invitations') as $invitationData) {
                    if (!empty($invitationData['email']) && !empty($invitationData['role'])) {
                        
                        // Verifica se já existe um convite pendente para este email
                        $isAlreadyInvited = $projeto->invitations()
                                                ->where('email', $invitationData['email'])
                                                ->where('status', 'pendente')
                                                ->exists();
                        
                        // Verifica se o usuário já é um membro confirmado do projeto
                        $isAlreadyMember = $projeto->users()->where('email', $invitationData['email'])->exists();

                        // Só cria o convite se o usuário não for membro e não tiver um convite pendente
                        if (!$isAlreadyInvited && !$isAlreadyMember) {
                            ProjetoInvitation::create([
                                'projeto_id' => $projeto->id,
                                'user_id'    => auth()->id(),
                                'email'      => $invitationData['email'],
                                'role'       => $invitationData['role'],
                                'token'      => Str::uuid(),
                                'status'     => 'pendente',
                            ]);
                        }
                    }
                }
            }

            // 4. Lógica para atualizar atividades e cronogramas (mantida)
            if ($request->has('atividades')) {
                $projeto->atividades()->delete(); // Apaga os antigos para simplicidade
                foreach ($request->atividades as $atividadeData) {
                    if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                        $projeto->atividades()->create($atividadeData);
                    }
                }
            }

            if ($request->has('cronograma')) {
                $projeto->cronogramas()->delete(); // Apaga os antigos
                foreach ($request->cronograma as $itemDataCronograma) {
                    if (!empty($itemDataCronograma['atividade']) && !empty($itemDataCronograma['mes_inicio']) && !empty($itemDataCronograma['mes_fim'])) {
                        $projeto->cronogramas()->create($itemDataCronograma);
                    }
                }
            }

            DB::commit();

            return redirect()->route('projetos.show', $projeto->id)->with('success', 'Projeto atualizado e novos convites enviados com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocorreu um erro ao atualizar o projeto: ' . $e->getMessage());
        }
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
        $projeto = Projeto::with('user', 'users')->findOrFail($id);
        $this->authorize('approveByNapex', $projeto);

        $validatedData = $request->validate([
            'aprovado_napex' => 'required|in:sim,nao',
            'motivo_napex' => 'nullable|string|required_if:aprovado_napex,nao|max:2000',
        ]);

        $projeto->fill($validatedData);
        $projeto->data_parecer_napex = now();

        if ($projeto->aprovado_napex === 'sim' && empty($projeto->numero_projeto)) {
            $ano = now()->year;

            $ultimoProjeto = Projeto::where('numero_projeto', 'like', $ano . '-%')
                                    ->orderBy('numero_projeto', 'desc')
                                    ->first();

            $proximoNumero = 1;
            if ($ultimoProjeto) {
                $ultimoSequencial = (int) substr($ultimoProjeto->numero_projeto, -3);
                $proximoNumero = $ultimoSequencial + 1;
            }

            $projeto->numero_projeto = $ano . '-' . str_pad($proximoNumero, 3, '0', STR_PAD_LEFT);
        }

        $projeto->save();

        $aluno = $projeto->user;
        $professores = $projeto->users->filter(fn($u) => str_starts_with($u->role, 'professor'));
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

        $projeto = Projeto::with('user', 'users')->findOrFail($id);
        $this->authorize('approveByCoordinator', $projeto);

        $validatedData = $request->validate([
            'aprovado_coordenador' => 'required|in:sim,nao',
            'motivo_coordenador' => 'nullable|string|required_if:aprovado_coordenador,nao|max:2000',
        ]);

        $projeto->fill($validatedData);
        $projeto->data_parecer_coordenador = now();
        
        $projeto->save();


        $aluno = $projeto->user;
        $professores = $projeto->users->filter(fn($u) => str_starts_with($u->role, 'professor'));
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

        $projeto = \App\Models\Projeto::with('users')->findOrFail($id);
        
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

        $professores = $projeto->users->filter(function ($user) {
            return str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador');
        });

        if ($professores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($professores, new \App\Notifications\ProjetoSubmetidoPeloAluno($projeto));
        }
        
  
        $avaliadores = $this->getEvaluationRecipients($projeto);
        
        if ($avaliadores->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($avaliadores, new \App\Notifications\PropostaEnviada($projeto));
        }
        
        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Projeto enviado para avaliação com sucesso!');
    }

    private function getEvaluationRecipients(Projeto $projeto)
    {
        // 1. Carrega o curso do aluno que criou o projeto (o criador é o user()).
        $projeto->load('user.curso');
    
        $cursoId = $projeto->user->curso_id;
    
        if (!$cursoId) {
            // Se o aluno não tem curso, apenas notifica o NAPEX
            return User::where('role', 'napex')->get();
        }
    
        // 2. Busca o coordenador do curso do aluno.
        $coordenador = User::where('role', 'coordenador')
            ->whereHas('cursosCoordenados', function ($query) use ($cursoId) {
                // Filtra usuários que coordenam o curso do aluno.
                $query->where('curso_id', $cursoId);
            })->first();
    
        // 3. Busca todos os usuários NAPEX
        $napexUsers = User::where('role', 'napex')->get();
    
        // 4. Combina o coordenador (se encontrado) e os usuários NAPEX
        $recipients = collect([]);
        if ($coordenador) {
            $recipients->push($coordenador);
        }
        
        // Garante que não haja duplicatas e retorna a coleção.
        $recipients = $recipients->merge($napexUsers)->unique('id');
    
        return $recipients;
    }

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
            $projeto = Projeto::with(['users', 'atividades', 'cronogramas', 'user', 'rejeicoes'])->findOrFail($id);

            $this->authorize('view', $projeto);

            $alunos = $projeto->users->where('role', 'aluno');
            
            $professores = $projeto->users->filter(function ($user) {
                return str_starts_with($user->role, 'professor') || str_starts_with($user->role, 'coordenador');
            });

            $pdf = Pdf::loadView('projetos.pdf', compact('projeto', 'alunos', 'professores'));
             $numero = $projeto->numero_projeto ?? "ID-{$projeto->id}";
            $nomeArquivo = "{$numero}-proposta.pdf";

            
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

    public function convidarParticipante(Request $request, Projeto $projeto)
    {
        $this->authorize('update', $projeto);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:aluno,professor',
        ]);

        $convidado = User::where('email', $validated['email'])->first();
        if ($convidado && $projeto->users()->where('user_id', $convidado->id)->exists()) {
            return back()->with('error', 'Este usuário já participa do projeto.');
        }

        if (ProjetoInvitation::where('projeto_id', $projeto->id)->where('email', $validated['email'])->where('status', 'pendente')->exists()) {
            return back()->with('error', 'Já existe um convite pendente para este email.');
        }

        $convite = ProjetoInvitation::create([
            'projeto_id' => $projeto->id,
            'user_id'    => auth()->id(),
            'email'      => $validated['email'],
            'role'       => $validated['role'],
            'token'      => Str::uuid(), 
            'status'     => 'pendente',
        ]);
        return back()->with('success', "Convite enviado com sucesso para {$validated['email']}!");
    }
}