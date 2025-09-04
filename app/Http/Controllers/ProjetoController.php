<?php

namespace App\Http\Controllers;

// Models utilizados pelo controller
use App\Models\Projeto;
use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Atividade;
use App\Models\Cronograma;
use App\Models\User; // Necessário para buscar usuários (ex: professores)
use App\Models\Rejeicao; // Para registrar rejeições de projetos
use App\Models\Curso;

// Requests para validação de formulários
use App\Http\Requests\StoreProjetoRequest;
use App\Http\Requests\UpdateProjetoRequest;

// Facades e Classes do Laravel e de pacotes
use Illuminate\Http\Request; // Para manipulação de requisições HTTP genéricas
use Illuminate\Database\QueryException; // Para tratamento de exceções do banco de dados (não explicitamente usado no try-catch, mas bom ter em mente)
use Barryvdh\DomPDF\Facade\Pdf; // Para geração de PDFs
// use Illuminate\Support\Facades\Response; // REMOVER: Não utilizado neste controller
// use PhpOffice\PhpWord\TemplateProcessor; // REMOVER: Não utilizado neste controller
// use Illuminate\Support\Facades\Storage; // REMOVER: Não utilizado neste controller
// Nota: Auth::user() ou auth()->user() não requerem 'use Illuminate\Support\Facades\Auth;' se estiver usando o helper global.

class ProjetoController extends Controller
{
    /**
     * Exibe uma lista de projetos com base nos filtros aplicados e no papel (role) do usuário.
     * Permite paginação e ordenação.
     *
     * @return \Illuminate\View\View
     */


    public function index(Request $request)
    {
        // Inicia a query base, já carregando a relação com 'resultado' para otimização
        $query = Projeto::with(['atividades', 'user', 'professores', 'resultado']);

        $user = auth()->user();

        // Filtros por papel (lógica para aluno e professor mantida)
        if ($user->role === 'aluno') {
            $query->where('user_id', $user->id);
        }

        if ($user->role === 'professor') {
            $query->whereHas('professores', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // --- LÓGICA DE VISUALIZAÇÃO PARA AVALIADORES (CORRIGIDA) ---
        if (in_array($user->role, ['napex', 'coordenador'])) {
            $query->where(function ($q) {
                // Regra 1: Mostra propostas 'entregue' E propostas já 'aprovadas'.
                // Isso garante que a proposta não suma logo após ser aprovada.
                $q->where(function ($sub) {
                    $sub->where('etapa', 'Proposta')->whereIn('status', ['entregue', 'aprovado']);
                })
                // Regra 2 (A CORREÇÃO ESTÁ AQUI): Mostra TUDO que já avançou para a etapa de 'Resultado' ou 'Concluído'.
                // Isso cobre o período em que o projeto aguarda o envio do relatório pelo aluno,
                // o período de avaliação do relatório e os projetos já finalizados.
                ->orWhereIn('etapa', ['Resultado', 'Concluído']);
            });
        }

        // --- SEÇÃO DE FILTROS GERAIS DA BUSCA (Seu código original mantido) ---

        // Filtro principal pela ETAPA
        if ($request->filled('etapa') && $request->etapa != 'todas') {
            $query->where('etapa', $request->etapa);
        }

        // Filtros por campos de texto
        if ($request->filled('cadastrado_por')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->cadastrado_por . '%');
            });
        }
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }
        if ($request->filled('periodo')) {
            $query->where('periodo', 'like', '%' . $request->periodo . '%');
        }

        // Filtro de status CONTEXTUAL à ETAPA selecionada
        if ($request->filled('status') && $request->status != 'todos') {
            $etapaSelecionada = $request->etapa;
            $statusSelecionado = $request->status;

            if ($etapaSelecionada === 'Resultado') {
                $query->whereHas('resultado', function ($q) use ($statusSelecionado) {
                    $q->where('status', $statusSelecionado);
                });
            } else {
                // Se a etapa for Proposta, Todas, ou não especificada, o filtro de status se aplica ao projeto.
                $query->where('projetos.status', $statusSelecionado);
            }
        }
        
        // Filtros de aprovação CONTEXTUAIS à ETAPA
        if ($request->filled('aprovado_napex')) {
            $aprovacao = $request->aprovado_napex;
            if ($request->etapa === 'Resultado') {
                $query->whereHas('resultado', fn($q) => $q->where('aprovado_napex', $aprovacao));
            } else {
                $query->where('aprovado_napex', $aprovacao);
            }
        }

        if ($request->filled('aprovado_coordenador')) {
            $aprovacao = $request->aprovado_coordenador;
            if ($request->etapa === 'Resultado') {
                $query->whereHas('resultado', fn($q) => $q->where('aprovado_coordenador', $aprovacao));
            } else {
                $query->where('aprovado_coordenador', $aprovacao);
            }
        }
        
        // Demais filtros originais mantidos
        if ($request->filled('data_inicio_de') && $request->filled('data_inicio_ate')) {
            $query->whereBetween('data_inicio', [$request->data_inicio_de, $request->data_inicio_ate]);
        }
        if ($request->filled('data_fim_de') && $request->filled('data_fim_ate')) {
            $query->whereBetween('data_fim', [$request->data_fim_de, $request->data_fim_ate]);
        }
        if ($request->filled('carga_min') || $request->filled('carga_max')) {
            // ... (sua lógica de carga horária)
        }

        // Lógica de ordenação mantida
        $ordenar = $request->input('ordenar', 'data_desc');
        if ($ordenar == 'data_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginação e resposta anti-cache mantidas
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
        $cursos = Curso::orderBy('nome')->get(); // Busca os cursos do banco de dados

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
        // Verifica se o usuário autenticado é um aluno
        // A Policy 'create' é reutilizada aqui, pois a permissão é a mesma.
        $this->authorize('create', Projeto::class);


        $data = $request->validated(); // Obtém os dados validados do request
        $data['status'] = 'editando'; // Define o status inicial do projeto

        // Lógica para upload de arquivo, se um arquivo foi enviado e é válido
        if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
            $file = $request->file('arquivo');
            // Gera um nome de arquivo único para evitar conflitos
            $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->extension();
            $file->move(public_path('arquivos_projetos'), $fileName); // Move o arquivo para a pasta public
            $data['arquivo'] = 'arquivos_projetos/' . $fileName; // Salva o caminho do arquivo
        }

        // Cria a string 'periodo_realizacao' formatada se as datas de início e fim foram fornecidas
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $inicio = date('d/m/Y', strtotime($request->input('data_inicio')));
            $fim = date('d/m/Y', strtotime($request->input('data_fim')));
            $data['periodo_realizacao'] = "$inicio a $fim";
        }

        $data['user_id'] = auth()->id(); // Associa o projeto ao aluno autenticado
        // $data['professor_id'] = $request->input('professor_id'); // OBS: Esta linha parece redundante se os professores são salvos na tabela 'professores' relacionada.
                                                                // Se a tabela 'projetos' não tem um campo 'professor_id' direto, esta linha é desnecessária.
                                                                // Avaliar se este campo existe e é usado na tabela 'projetos'.

        $projeto = Projeto::create($data); // Cria o projeto principal

        // Salva os alunos relacionados, se houver
        if ($request->has('alunos')) {
            foreach ($request->alunos as $alunoData) { // Renomeado para $alunoData para clareza
                // Validação adicional para dados do aluno pode ser necessária aqui
                if (!empty($alunoData['nome']) && !empty($alunoData['ra'])) { // Exemplo de validação simples
                    $projeto->alunos()->create($alunoData);
                }
            }
        }

        // Salva os professores relacionados, se houver
        if ($request->has('professores')) {
            $professorIds = []; // Para verificar duplicidade de professores

            foreach ($request->professores as $professorData) {
                // Verifica se o ID do professor foi fornecido
                if (empty($professorData['id'])) continue; // Pula se o ID do professor não estiver presente

                // Verifica se o professor já foi adicionado para evitar duplicidade
                if (in_array($professorData['id'], $professorIds)) {
                    return redirect()->back()
                        ->withInput() // Mantém os dados do formulário
                        ->with('error', 'Você tentou adicionar o mesmo professor mais de uma vez.');
                }
                $professorIds[] = $professorData['id'];

                $userProfessor = User::find($professorData['id']); // Busca o usuário professor pelo ID
                if ($userProfessor) {
                    // Cria o registro na tabela 'professores' (relacionada ao projeto)
                    $projeto->professores()->create([
                        'nome' => $userProfessor->name,
                        'email' => $userProfessor->email,
                        'area' => $professorData['area'] ?? null, // Adiciona área se fornecida
                        'user_id' => $userProfessor->id, // Chave estrangeira para o usuário professor
                    ]);
                }
            }
        }

        // Salva as atividades relacionadas, se houver
        if ($request->has('atividades')) {
            foreach ($request->atividades as $atividadeData) { // Renomeado para $atividadeData
                 if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) { // Exemplo
                    $projeto->atividades()->create($atividadeData);
                 }
            }
        }

        if ($request->has('cronograma') && is_array($request->cronograma)) {
            foreach ($request->cronograma as $itemDataCronograma) {
                // A validação do Laravel já deve ter tratado campos obrigatórios.
                // Esta verificação !empty() é uma segurança adicional se, por exemplo,
                // nem todos os campos fossem estritamente 'required' pela validação em todos os cenários.
                // Se a validação já garante que são 'required', este if pode ser simplificado
                // ou focar em outras lógicas de negócio, se houver.

                if (!empty($itemDataCronograma['atividade']) &&
                    !empty($itemDataCronograma['mes_inicio']) &&
                    !empty($itemDataCronograma['mes_fim'])) {

                    // Cria o item de cronograma associado ao projeto.
                    // Certifique-se de que seu modelo Cronograma tenha 'atividade', 'mes_inicio', 'mes_fim'
                    // (e 'projeto_id', que é tratado pela relação) no array $fillable.
                    $projeto->cronogramas()->create([
                        'atividade'  => $itemDataCronograma['atividade'],
                        'mes_inicio' => $itemDataCronograma['mes_inicio'],
                        'mes_fim'    => $itemDataCronograma['mes_fim'],
                        // Adicione quaisquer outros campos do cronograma que você precise salvar aqui.
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
    public function show($id)
    {
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'rejeicoes', 'user'])->findOrFail($id);

   
        $user = auth()->user();
        if (in_array($user->role, ['napex', 'coordenador'])) {
            // Se o avaliador tentar ver uma proposta que não está na sua fila, bloqueie.
            if (!in_array($projeto->status, ['entregue', 'aprovado'])) {
                abort(403, 'Acesso não autorizado para este status de proposta.');
            }
        }
        $this->authorize('view', $projeto);

            $response = response(view('projetos.show', compact('projeto')));
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
        
        // <<< ADICIONE ESTA LINHA >>>
        $cursos = Curso::orderBy('nome')->get(); // Busca todos os cursos do banco

        // <<< ADICIONE 'cursos' AO COMPACT >>>
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
        // 1. Carrega o projeto que será atualizado
        $projeto = Projeto::with('professores')->findOrFail($id);

        // 2. Autoriza a ação usando a ProjetoPolicy.
        //    Esta única linha substitui todos os 'if's de permissão.
        $this->authorize('update', $projeto);
        
        // 3. Pega os dados validados do formulário
        $data = $request->validated();
        
        // 4. Atualiza os campos principais do projeto
        $projeto->update($data);
    
        // 5. Atualiza as relações (alunos, professores, etc.)
        //    A lógica de "deletar e recriar" é mantida.
        
        // Atualizar alunos
        $projeto->alunos()->delete();
        if ($request->has('alunos')) {
            foreach ($request->alunos as $alunoData) {
                if (!empty($alunoData['nome']) && !empty($alunoData['ra'])) {
                    $projeto->alunos()->create($alunoData);
                }
            }
        }
    
        // Atualizar professores
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
    
        // Atualizar atividades
        $projeto->atividades()->delete();
        if ($request->has('atividades')) {
            foreach ($request->atividades as $atividadeData) {
                if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                    $projeto->atividades()->create($atividadeData);
                }
            }
        }
    
        // Atualizar cronograma
        $projeto->cronogramas()->delete();
        if ($request->has('cronograma') && is_array($request->cronograma)) {
            foreach ($request->cronograma as $cronogramaItem) {
                // A validação já garante que os campos existem
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
    $projeto = Projeto::findOrFail($id);

    // Usa o método 'approveByNapex' da sua Policy
    $this->authorize('approveByNapex', $projeto);

    if ($projeto->status !== 'entregue') {
        return redirect()->route('projetos.show', $projeto->id)
                         ->with('error', 'Este projeto não está com status "Entregue" e não pode ser avaliado no momento.');
    }

    $validatedData = $request->validate([
        'aprovado_napex' => 'required|in:sim,nao',
        'motivo_napex' => 'nullable|string|required_if:aprovado_napex,nao|max:2000',
        'numero_projeto' => 'nullable|string|max:255',
    ]);

    $projeto->aprovado_napex = $validatedData['aprovado_napex'];
    $projeto->motivo_napex = $validatedData['motivo_napex'] ?? null;
    $projeto->data_parecer_napex = now();
    if ($projeto->aprovado_napex === 'sim' && isset($validatedData['numero_projeto'])) {
        $projeto->numero_projeto = $validatedData['numero_projeto'];
    }

    if ($projeto->aprovado_napex === 'nao') {
        $this->registrarRejeicao($projeto, $projeto->motivo_napex, 'napex');
        $projeto->status = 'editando'; 
        $projeto->save();
        return redirect()->route('projetos.index')
                         ->with('success', 'Projeto NÃO APROVADO pelo NAPEx. Status alterado para "Editando" e devolvido ao aluno.');
    } else { // NAPEx APROVOU (aprovado_napex === 'sim')
        
        // **PONTO CHAVE DA SUA REGRA DE NEGÓCIO:**
        // O status do projeto SÓ muda para 'aprovado' se AMBOS, NAPEx E Coordenador, tiverem aprovado.
        if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
            $projeto->status = 'aprovado';
            $projeto->etapa = 'Resultado'; 
        }
        // Se $projeto->aprovado_coordenador !== 'sim' (ou seja, está 'pendente' ou 'nao'),
        // o status do projeto NÃO é alterado aqui. Se ele era 'entregue', continuará 'entregue'.
        
        $projeto->save();
        return redirect()->route('projetos.show', $projeto->id)
                         ->with('success', 'Parecer do NAPEx salvo com sucesso.');
    }
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
    $projeto = Projeto::findOrFail($id);

    
    $this->authorize('approveByCoordinator', $projeto);

    if ($projeto->status !== 'entregue') {
        return redirect()->route('projetos.show', $projeto->id)
                         ->with('error', 'Este projeto não está com status "Entregue" e não pode ser avaliado no momento.');
    }

    $validatedData = $request->validate([
        'aprovado_coordenador' => 'required|in:sim,nao',
        'motivo_coordenador' => 'nullable|string|required_if:aprovado_coordenador,nao|max:2000',
    ]);

    $projeto->aprovado_coordenador = $validatedData['aprovado_coordenador'];
    $projeto->motivo_coordenador = $validatedData['motivo_coordenador'] ?? null;
    $projeto->data_parecer_coordenador = now();

    if ($projeto->aprovado_coordenador === 'nao') {
        $this->registrarRejeicao($projeto, $projeto->motivo_coordenador, 'coordenador');
        $projeto->status = 'editando';
        $projeto->save();
        return redirect()->route('projetos.index')
                         ->with('success', 'Projeto NÃO APROVADO pela Coordenação. Status alterado para "Editando" e devolvido ao aluno.');
    } else { // Coordenador APROVOU (aprovado_coordenador === 'sim')

        // **PONTO CHAVE DA SUA REGRA DE NEGÓCIO:**
        // O status do projeto SÓ muda para 'aprovado' se AMBOS, NAPEx E Coordenador, tiverem aprovado.
        if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
            $projeto->status = 'aprovado';
            $projeto->etapa = 'Resultado'; 
        }
        // Se $projeto->aprovado_napex !== 'sim' (ou seja, está 'pendente' ou 'nao'),
        // o status do projeto NÃO é alterado aqui. Se ele era 'entregue', continuará 'entregue'.

        $projeto->save();
        return redirect()->route('projetos.show', $projeto->id)
                         ->with('success', 'Parecer do Coordenador salvo com sucesso.');
    }
}

    /**
     * Limpa campos de aprovação e redefine o status do projeto para 'editando'.
     * Usado quando um projeto é rejeitado para permitir nova edição pelo aluno.
     * ATENÇÃO: Este método foi renomeado para limparAprovacoesParciais e a lógica foi ajustada.
     * A função original 'limparAprovacoes' está abaixo, mas pode ser muito drástica.
     *
     * @param  \App\Models\Projeto  $projeto
     * @param  string $origemRejeicao 'napex' ou 'coordenador'
     * @return void
     */
    private function limparAprovacoesParciais($projeto, $origemRejeicao)
    {
        $updateData = [
            'status' => 'editando', // Volta para edição para o aluno corrigir
            // 'data_entrega' => null, // Decide se a data de entrega original deve ser mantida ou resetada
        ];

        if ($origemRejeicao === 'napex') {
            $updateData['aprovado_napex'] = 'pendente'; // Ou 'rejeitado', dependendo do fluxo desejado
            $updateData['motivo_napex'] = $projeto->motivo_napex; // Mantém o motivo da rejeição
            // $updateData['data_parecer_napex'] = null; // Data do parecer de rejeição é mantida
            // Se NAPEx rejeita, a aprovação do coordenador (se existia) também é invalidada?
            // Isso depende da regra de negócio. Se sim:
            // $updateData['aprovado_coordenador'] = 'pendente';
            // $updateData['motivo_coordenador'] = null;
            // $updateData['data_parecer_coordenador'] = null;
        } elseif ($origemRejeicao === 'coordenador') {
            $updateData['aprovado_coordenador'] = 'pendente'; // Ou 'rejeitado'
            $updateData['motivo_coordenador'] = $projeto->motivo_coordenador; // Mantém o motivo
            // $updateData['data_parecer_coordenador'] = null;
            // Se Coordenador rejeita, a aprovação do NAPEx (se existia) também é invalidada?
            // $updateData['aprovado_napex'] = 'pendente';
            // $updateData['motivo_napex'] = null;
            // $updateData['data_parecer_napex'] = null;
        }
        
        // Se qualquer um rejeita, o número do projeto pode ser resetado se ele só é atribuído na aprovação final.
        // $updateData['numero_projeto'] = null;


        $projeto->update($updateData);
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
        // 1. Carrega o projeto que será excluído
        $projeto = Projeto::findOrFail($id);

        // 2. Autoriza a exclusão usando a ProjetoPolicy.
        //    Esta única linha substitui AMBOS os 'if's de permissão que você tinha antes.
        $this->authorize('delete', $projeto);

        // 3. Executa a exclusão (sua lógica original é mantida)
        try {
            // Lógica para deletar arquivos associados
            if ($projeto->arquivo && file_exists(public_path($projeto->arquivo))) {
                unlink(public_path($projeto->arquivo));
            }

            $projeto->delete(); // Deleta o projeto e suas relações em cascata

            return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');

        } catch (\Exception $e) {
            // Opcional: Logar o erro para depuração
            // Log::error("Erro ao excluir projeto {$id}: " . $e->getMessage());
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
        // 1. Carrega o projeto
        $projeto = Projeto::findOrFail($id);
        
        // 2. Autoriza a ação usando a Policy.
        //    A sua policy 'submit' já verifica o dono do projeto e o status 'editando'.
        $this->authorize('submit', $projeto);

        // 3. Executa a ação (o resto do seu código)
        $projeto->status = 'entregue';
        $projeto->data_entrega = now();
        $projeto->aprovado_napex = 'pendente';
        $projeto->motivo_napex = null;
        $projeto->data_parecer_napex = null;
        $projeto->aprovado_coordenador = 'pendente';
        $projeto->motivo_coordenador = null;
        $projeto->data_parecer_coordenador = null;

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
        // 1. Carrega o projeto
        $projeto = Projeto::findOrFail($id);

        // 2. Autoriza a ação usando a nova regra 'revertToEditing' na Policy.
        //    Esta linha substitui todo o bloco de 'if's de permissão e estado.
        $this->authorize('revertToEditing', $projeto);
    
        // 3. Executa a ação principal (lógica mantida)
        $projeto->status = 'editando';
        $projeto->data_entrega = null; // Limpa a data de entrega
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
            'motivo' => $motivo ?? 'Motivo não especificado.', // Garante que não seja nulo
            'data_rejeicao' => now(),
            'autor' => $autor, // 'napex' ou 'coordenador'
        ]);
    }
    
    /**
     * Exporta uma lista filtrada de projetos para um arquivo PDF.
     * A autorização é tratada pela ProjetoPolicy@exportGeneralPdf.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportarPdf(Request $request)
    {
        // 1. Autoriza a ação usando a Policy. Substitui o 'if'.
        $this->authorize('exportGeneralPdf', Projeto::class);

        // 2. O restante da sua lógica para buscar dados e gerar o PDF é mantido.
        $query = Projeto::query()->with(['user', 'atividades', 'professores']);

        $query->whereIn('status', ['entregue', 'aprovado']);

        // Aplicação dos filtros da requisição (código original mantido)
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('cadastrado_por')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->cadastrado_por . '%');
            });
        }

        if ($request->filled('data_inicio_de') && $request->filled('data_inicio_ate')) {
            $query->whereBetween('data_inicio', [$request->data_inicio_de, $request->data_inicio_ate]);
        } elseif ($request->filled('data_inicio_de')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio_de);
        } elseif ($request->filled('data_inicio_ate')) {
            $query->whereDate('data_inicio', '<=', $request->data_inicio_ate);
        }

        if ($request->filled('data_fim_de') && $request->filled('data_fim_ate')) {
            $query->whereBetween('data_fim', [$request->data_fim_de, $request->data_fim_ate]);
        } elseif ($request->filled('data_fim_de')) {
            $query->whereDate('data_fim', '>=', $request->data_fim_de);
        } elseif ($request->filled('data_fim_ate')) {
            $query->whereDate('data_fim', '<=', $request->data_fim_ate);
        }

        if ($request->filled('carga_min') || $request->filled('carga_max')) {
            $query->whereHas('atividades', function ($q) use ($request) {
                $q->selectRaw('projeto_id, SUM(carga_horaria) as soma_carga_horaria')
                  ->groupBy('projeto_id');

                if ($request->filled('carga_min')) {
                    $q->havingRaw('SUM(carga_horaria) >= ?', [$request->carga_min]);
                }
                if ($request->filled('carga_max')) {
                    $q->havingRaw('SUM(carga_horaria) <= ?', [$request->carga_max]);
                }
            });
        }

        if ($request->filled('status') && !in_array($request->status, ['--', 'todos', null], true) ) {
            $query->where('status', $request->status);
        }

        if ($request->filled('aprovado_napex') && !in_array($request->aprovado_napex, ['--', 'todos', null], true) ) {
            $query->where('aprovado_napex', $request->aprovado_napex);
        }

        if ($request->filled('aprovado_coordenador') && !in_array($request->aprovado_coordenador, ['--', 'todos', null], true) ) {
            $query->where('aprovado_coordenador', $request->aprovado_coordenador);
        }

        $projetos = $query->orderBy('created_at', 'desc')->get();
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
        // 1. Carrega o projeto
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'user', 'rejeicoes'])->findOrFail($id);

        // 2. VERIFICA A PERMISSÃO!
        //    Usa a regra 'view' da sua ProjetoPolicy para garantir que o usuário pode ver (e portanto baixar) este projeto.
        $this->authorize('view', $projeto);

        // 3. Se a autorização passar, o resto do código é executado normalmente.
        $pdf = Pdf::loadView('projetos.pdf', compact('projeto'));

        $nomeArquivo = "proposta_extensionista_{$projeto->id}.pdf";
        $nomeArquivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeArquivo);

        return $pdf->download($nomeArquivo);
    }
}