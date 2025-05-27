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
    public function index()
    {
        // Inicia a query base para projetos, já carregando algumas relações para otimização (eager loading)
        $query = Projeto::with(['atividades', 'user', 'professores']);
    
        $user = auth()->user(); // Obtém o usuário autenticado
    
        // Filtra projetos com base no papel do usuário
        if ($user->role === 'aluno') {
            // Alunos veem apenas os projetos que eles criaram
            $query->where('user_id', $user->id);
        }
    
        if ($user->role === 'professor') {
            // Professores veem projetos aos quais estão vinculados
            $query->whereHas('professores', function ($q) use ($user) {
                $q->where('user_id', $user->id); // Filtra pela tabela 'professores' relacionada ao projeto
            });
        }
    
        // NAPEX e Coordenadores têm uma lógica de filtro de status específica
        if (in_array($user->role, ['napex', 'coordenador'])) {
            // Se um status específico ('entregue' ou 'aprovado') foi passado na URL
            if (request()->filled('status')) {
                if (in_array(request('status'), ['entregue', 'aprovado'])) {
                    $query->where('status', request('status'));
                } else {
                    // Se um status inválido foi passado, mostra os permitidos por padrão para esses perfis
                    $query->whereIn('status', ['entregue', 'aprovado']);
                }
            } else {
                // Se nenhum status foi passado, mostra os permitidos por padrão
                $query->whereIn('status', ['entregue', 'aprovado']);
            }
        }

        // Aplica filtros gerais da requisição, se presentes
        if (request('cadastrado_por')) {
            // Filtra pelo nome do usuário que cadastrou o projeto
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . request('cadastrado_por') . '%');
            });
        }
    
        if (request('titulo')) {
            $query->where('titulo', 'like', '%' . request('titulo') . '%');
        }
    
        if (request('periodo')) {
            $query->where('periodo', 'like', '%' . request('periodo') . '%');
        }
    
        // Este filtro de status é mais genérico e pode ser aplicado por outros papéis ou sobrescrever o anterior
        // É importante notar que o filtro de status para napex/coordenador já foi aplicado.
        // Se este request('status') vier de um filtro geral, pode causar comportamento inesperado
        // se não for a intenção. Idealmente, a lógica de status deveria ser mais centralizada ou condicionada.
        if (request('status') && !in_array($user->role, ['napex', 'coordenador'])) { // Adicionado condição para não conflitar
             $query->where('status', request('status'));
        }
    
        if (request('aprovado_napex') === 'sim') {
            $query->where('aprovado_napex', 'sim');
        }
    
        if (request('aprovado_coordenador') === 'sim') {
            $query->where('aprovado_coordenador', 'sim');
        }
    
        // Filtro para projetos com aprovação final (ambos napex e coordenador)
        if (request('aprovacao_final') === 'sim') {
            $query->where('aprovado_napex', 'sim')->where('aprovado_coordenador', 'sim');
        }
    
        // Filtro de intervalo para data_inicio
        if (request('data_inicio_de') && request('data_inicio_ate')) {
            $query->whereBetween('data_inicio', [request('data_inicio_de'), request('data_inicio_ate')]);
        } elseif (request('data_inicio_de')) {
            $query->whereDate('data_inicio', '>=', request('data_inicio_de'));
        } elseif (request('data_inicio_ate')) {
            $query->whereDate('data_inicio', '<=', request('data_inicio_ate'));
        }
    
        // Filtro de intervalo para data_fim
        if (request('data_fim_de') && request('data_fim_ate')) {
            $query->whereBetween('data_fim', [request('data_fim_de'), request('data_fim_ate')]);
        } elseif (request('data_fim_de')) {
            $query->whereDate('data_fim', '>=', request('data_fim_de'));
        } elseif (request('data_fim_ate')) {
            $query->whereDate('data_fim', '<=', request('data_fim_ate'));
        }
        
        // Lógica de ordenação dos resultados
        $ordenar = request('ordenar');
        if ($ordenar == 'data_asc') {
            $query->orderBy('created_at', 'asc'); // Mais antigos primeiro
        } else {
            // Padrão (se 'data_desc' ou nulo): mais novos no topo
            $query->orderBy('created_at', 'desc');
        }

        // Filtro de carga horária total do projeto (soma das cargas horárias das atividades)
        // Utiliza whereHas para filtrar projetos com base na soma das cargas horárias de suas atividades.
        // Os havingRaw permitem aplicar condições na soma (SUM) calculada.
        // A verificação 'IS NULL OR ...' permite que o filtro seja opcional (se carga_min/max não for passado, não filtra por ele).
        if (request()->filled('carga_min') || request()->filled('carga_max')) { // Executa apenas se um dos filtros de carga foi enviado
            $query->whereHas('atividades', function ($q) {
                $q->selectRaw('projeto_id, SUM(carga_horaria) as soma_carga_horaria') // Nome do alias corrigido para clareza
                  ->groupBy('projeto_id');
                
                if (request()->filled('carga_min')) {
                    $q->havingRaw('SUM(carga_horaria) >= ?', [request('carga_min')]);
                }
                if (request()->filled('carga_max')) {
                    $q->havingRaw('SUM(carga_horaria) <= ?', [request('carga_max')]);
                }
            });
        }
        
        // Pagina os resultados e anexa os parâmetros da query atual aos links de paginação
        $projetos = $query->paginate(10)->appends(request()->query());
    
        return view('projetos.index', compact('projetos'));
    }
    
    /**
     * Exibe o formulário para criação de um novo projeto.
     * Apenas alunos podem criar projetos.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Verifica se o usuário autenticado é um aluno
        if (auth()->user()->role !== 'aluno') {
            abort(403, 'Apenas alunos podem criar projetos.'); // Retorna erro 403 (Proibido)
        }

        // Busca todos os usuários com papel de 'professor' para popular um seletor no formulário
        $professores = User::where('role', 'professor')->orderBy('name')->get(); // Adicionado orderBy
        return view('projetos.create', compact('professores'));
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
        if (auth()->user()->role !== 'aluno') {
            abort(403, 'Apenas alunos podem cadastrar projetos.');
        }

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

        // Salva os itens do cronograma relacionados, se houver
        if ($request->has('cronograma')) {
            foreach ($request->cronograma as $cronogramaItem) { // Renomeado para $cronogramaItem
                if (!empty($cronogramaItem['atividade']) && !empty($cronogramaItem['mes'])) { // Exemplo
                    $projeto->cronogramas()->create($cronogramaItem);
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
        return view('projetos.show', compact('projeto'));
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
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas'])->findOrFail($id);
        $user = auth()->user();
        $userRole = $user->role;

        // Regra de negócio: Bloqueia edição se NAPEx ou Coordenador já aprovou o projeto
        if ($projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
            // Adiciona verificação para permitir que NAPEx/Coordenador editem campos específicos de avaliação mesmo após aprovação de um deles
            // Esta lógica de redirecionamento pode ser muito restritiva se eles precisarem corrigir algo no parecer.
            // Talvez uma view de "avaliação" separada seja melhor do que o "edit" geral.
            // Por ora, mantendo a lógica original:
            if (!in_array($userRole, ['napex', 'coordenador'])) { // Permite que napex/coord vejam o form mesmo aprovado por um, mas não editem campos do projeto.
                 return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto já possui aprovação e não pode mais ser editado por você.');
            }
        }
        
        // Lógica de permissão específica para alunos
        if ($userRole === 'aluno') {
            if ($projeto->user_id !== $user->id) { // Aluno só pode editar seus próprios projetos
                return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para editar este projeto.');
            }
            if ($projeto->status === 'entregue' || $projeto->status === 'aprovado') { // Aluno não pode editar se já entregue ou aprovado
                return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto foi entregue ou aprovado e não pode mais ser editado.');
            }
        }

        // Lógica de permissão específica para professores
        if ($userRole === 'professor') {
            $isProfessorLinked = $projeto->professores->contains('user_id', $user->id);
            if (!$isProfessorLinked) { // Professor só edita projetos aos quais está vinculado
                return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para editar este projeto.');
            }
            // Professor (assim como aluno) não pode editar se o projeto já foi 'entregue' ou 'aprovado'.
            // A aprovação por NAPEx/Coordenador já bloqueia acima.
            if ($projeto->status === 'entregue' || $projeto->status === 'aprovado') {
                 return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto foi entregue ou aprovado e não pode mais ser editado.');
            }
        }
        
        // Busca todos os usuários com papel de 'professor' para o formulário de edição
        $professores = User::where('role', 'professor')->orderBy('name')->get(); // Renomeado para evitar conflito com $projeto->professores

        return view('projetos.edit', compact('projeto', 'professores')); // Passa a variável renomeada
    }

    /**
     * Atualiza um projeto existente no banco de dados.
     * Utiliza UpdateProjetoRequest para validação.
     * Adota a estratégia de "delete and recreate" para relações (alunos, professores, etc.).
     *
     * @param  \App\Http\Requests\UpdateProjetoRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProjetoRequest $request, $id)
    {
        $projeto = Projeto::with('professores')->findOrFail($id); // Carrega relação para verificação
        $user = auth()->user();
        $userRole = $user->role;
    
        // Lógica de permissão para atualização (similar ao edit)
        // Aluno não pode atualizar se status for 'entregue' ou 'aprovado' ou se não for o dono.
        if ($userRole === 'aluno') {
            if ($projeto->user_id !== $user->id) {
                 return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para atualizar este projeto.');
            }
            if ($projeto->status === 'entregue' || $projeto->status === 'aprovado') {
                return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto foi entregue ou aprovado e não pode mais ser atualizado.');
            }
        }
        // Professor não pode atualizar se não estiver vinculado, ou se status for 'entregue' ou 'aprovado'.
        if ($userRole === 'professor') {
            $isProfessorLinked = $projeto->professores->contains('user_id', $user->id);
            if (!$isProfessorLinked) {
                return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para atualizar este projeto.');
            }
            if ($projeto->status === 'entregue' || $projeto->status === 'aprovado') {
                return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto foi entregue ou aprovado e não pode mais ser atualizado.');
            }
        }

        // NAPEX e Coordenador podem ter campos específicos que eles podem atualizar (campos de avaliação).
        // Outros campos do projeto podem ser bloqueados para eles aqui se necessário,
        // dependendo das regras de negócio para quem pode alterar o quê após a entrega.
        // A validação em UpdateProjetoRequest deve refletir isso.

        $data = $request->validated(); // Obtém dados validados

        // Sanitização para campos de data que podem vir vazios ou com formato incorreto do formulário
        // e não são obrigatórios pela UpdateProjetoRequest para todos os cenários.
        // Se UpdateProjetoRequest já garante o formato YYYY-MM-DD para esses campos quando presentes, esta sanitização pode ser redundante.
        foreach (['data_entrega', 'data_parecer_napex', 'data_parecer_coordenador'] as $campo) {
            if (isset($data[$campo]) && !empty($data[$campo]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$campo])) {
                // Se o campo está presente, não está vazio, mas não bate com YYYY-MM-DD, anula para evitar erro no BD.
                // Ou poderia tentar converter para YYYY-MM-DD se o formato de entrada for conhecido e diferente.
                unset($data[$campo]);
            } elseif (isset($data[$campo]) && empty($data[$campo])) {
                // Se o campo foi enviado como vazio, garante que seja nulo no banco.
                $data[$campo] = null;
            }
        }
        
        $projeto->update($data); // Atualiza os dados principais do projeto
    
        // Estratégia "Delete and recreate" para atualizar relações.
        // Pode ser otimizado com sync() ou updateOrCreate() para cenários mais complexos,
        // mas para formulários que reenviam todos os itens, delete/recreate é mais simples.

        // Atualizar alunos
        if ($userRole === 'aluno' || $userRole === 'admin') { // Apenas aluno criador (ou admin) pode mudar alunos
            $projeto->alunos()->delete();
            if ($request->has('alunos')) {
                foreach ($request->alunos as $alunoData) {
                     if (!empty($alunoData['nome']) && !empty($alunoData['ra'])) {
                        $projeto->alunos()->create($alunoData);
                     }
                }
            }
        }
    
        // Atualizar professores (similarmente, controle de quem pode alterar)
        if ($userRole === 'aluno' || $userRole === 'admin') {
            $projeto->professores()->delete();
            if ($request->has('professores')) {
                $professorIds = []; // Para verificar duplicidade
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
        }
    
        // Atualizar atividades (similarmente, controle de quem pode alterar)
        if ($userRole === 'aluno' || $userRole === 'admin') {
            $projeto->atividades()->delete();
            if ($request->has('atividades')) {
                foreach ($request->atividades as $atividadeData) {
                    if (!empty($atividadeData['o_que_fazer']) && !empty($atividadeData['como_fazer'])) {
                        $projeto->atividades()->create($atividadeData);
                    }
                }
            }
        }
    
        // Atualizar cronograma (similarmente, controle de quem pode alterar)
        if ($userRole === 'aluno' || $userRole === 'admin') {
            $projeto->cronogramas()->delete();
            if ($request->has('cronograma')) {
                foreach ($request->cronograma as $cronogramaItem) {
                    if (!empty($cronogramaItem['atividade']) && !empty($cronogramaItem['mes'])) {
                        $projeto->cronogramas()->create($cronogramaItem);
                    }
                }
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
    if (auth()->user()->role !== 'napex') {
        abort(403, 'Apenas NAPEx pode avaliar nesta etapa.');
    }

    $projeto = Projeto::findOrFail($id);

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
    if (auth()->user()->role !== 'coordenador') {
        abort(403, 'Apenas Coordenador pode avaliar nesta etapa.');
    }

    $projeto = Projeto::findOrFail($id);

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
     * Exclui um projeto. Apenas o aluno que criou pode excluir.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $projeto = Projeto::findOrFail($id);
        $user = auth()->user();

        // Verifica se o usuário é o dono do projeto e tem o papel 'aluno'
        // Ou se é um admin/papel com permissão global de exclusão
        if (!($user->role === 'aluno' && $projeto->user_id === $user->id) /* && $user->role !== 'admin' */) {
            // Adicionar verificação de admin se necessário: || $user->role === 'admin'
            abort(403, 'Você não tem permissão para excluir este projeto.');
        }

        // Adicionalmente, pode-se verificar se o projeto já foi aprovado ou está em fase de avaliação,
        // para impedir a exclusão nesses casos, dependendo da regra de negócio.
        if ($projeto->status === 'aprovado' || $projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
            return redirect()->route('projetos.index')->with('error', 'Projetos em avaliação ou já aprovados não podem ser excluídos.');
        }


        try {
            // Lógica para deletar arquivos associados do storage, se houver
            // if ($projeto->arquivo && Storage::disk('public')->exists(str_replace('arquivos_projetos/', '', $projeto->arquivo))) {
            //    Storage::disk('public')->delete(str_replace('arquivos_projetos/', '', $projeto->arquivo));
            // }
            // Nota: O código atual salva em public_path(), não em storage/app/public. A lógica de deleção seria diferente.
            if ($projeto->arquivo && file_exists(public_path($projeto->arquivo))) {
                 unlink(public_path($projeto->arquivo));
            }

            $projeto->delete(); // Deleta o projeto e suas relações em cascata (se configurado no DB/model)
            return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');

        } catch (\Exception $e) {
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
        $projeto = Projeto::findOrFail($id);
        $user = auth()->user();

        // Permissão: apenas o aluno dono do projeto e se o projeto estiver 'editando' ou 'rejeitado' (para re-entrega)
        if (!($user->role === 'aluno' && $projeto->user_id === $user->id)) {
            abort(403, 'Você não tem permissão para enviar este projeto.');
        }
        if (!in_array($projeto->status, ['editando', 'rejeitado'])) { // 'rejeitado' pode ser um status se desejar
            return redirect()->route('projetos.show', $projeto->id)->with('error', 'Este projeto não pode ser enviado no estado atual.');
        }

        $projeto->status = 'entregue';
        $projeto->data_entrega = now(); // Registra a data/hora da entrega
        // Ao entregar, as aprovações anteriores devem ser resetadas?
        // Se um projeto foi rejeitado e está sendo reenviado, sim.
        $projeto->aprovado_napex = 'pendente';
        $projeto->motivo_napex = null;
        $projeto->data_parecer_napex = null;
        $projeto->aprovado_coordenador = 'pendente';
        $projeto->motivo_coordenador = null;
        $projeto->data_parecer_coordenador = null;
        // $projeto->numero_projeto = null; // Se o número só é dado na aprovação final

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
    public function voltarParaEdicao($id)
    {
        $projeto = Projeto::findOrFail($id);
        $user = auth()->user();

        // Permissão: Aluno dono, ou professor vinculado, ou NAPEX/Coordenador/Admin
        $canReturn = false;
        if ($user->role === 'aluno' && $projeto->user_id === $user->id) {
            $canReturn = true;
        } elseif ($user->role === 'professor' && $projeto->professores->contains('user_id', $user->id)) {
            $canReturn = true;
        } elseif (in_array($user->role, ['napex', 'coordenador', 'admin'])) { // Adicionado admin
            $canReturn = true;
        }

        if (!$canReturn) {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
    
        // Impede retorno à edição se já tiver sido aprovado por NAPEx ou Coordenação, ou se o status final for 'aprovado'
        if ($projeto->status === 'aprovado' || $projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
            return redirect()->route('projetos.show', $projeto->id)->with('error', 'Não é possível voltar para edição após uma aprovação ou se o projeto já está aprovado.');
        }
        
        // Permite voltar para edição apenas se o status for 'entregue' (ou talvez 'rejeitado' se for um fluxo diferente)
        if ($projeto->status !== 'entregue') {
             return redirect()->route('projetos.show', $projeto->id)->with('error', 'Apenas projetos com status "entregue" (e não avaliados) podem voltar para edição.');
        }
    
        $projeto->status = 'editando';
        $projeto->data_entrega = null; // Limpa a data de entrega
        // As rejeições anteriores (se houver) devem ser mantidas para histórico.
        // As aprovações parciais são implicitamente 'pendente' se o status volta para 'editando'.
        $projeto->save();
    
        return redirect()->route('projetos.edit', $projeto->id)->with('success', 'Projeto liberado para edição novamente.');
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
     * Acessível apenas para NAPEx e Coordenadores.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportarPdf(Request $request)
    {
        // Verifica permissão de acesso
        if (!in_array(auth()->user()->role, ['napex', 'coordenador', 'admin'])) { // Adicionado admin
            abort(403, 'Acesso negado a esta funcionalidade.');
        }

        // Inicia a query com eager loading
        $query = Projeto::query()->with(['user', 'atividades', 'professores']); // Adicionado professores

        // Filtro base: Somente status 'entregue' ou 'aprovado' para relatórios
        $query->whereIn('status', ['entregue', 'aprovado']);

        // Aplicação dos filtros da requisição
        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('cadastrado_por')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->cadastrado_por . '%');
            });
        }

        // Filtros de data
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

        // Filtro de carga horária (corrigido e melhorado)
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

        // Filtros de status e aprovações (com '--' para "todos")
        if ($request->filled('status') && !in_array($request->status, ['--', 'todos', null], true) ) {
            $query->where('status', $request->status);
        }

        if ($request->filled('aprovado_napex') && !in_array($request->aprovado_napex, ['--', 'todos', null], true) ) {
            $query->where('aprovado_napex', $request->aprovado_napex);
        }

        if ($request->filled('aprovado_coordenador') && !in_array($request->aprovado_coordenador, ['--', 'todos', null], true) ) {
            $query->where('aprovado_coordenador', $request->aprovado_coordenador);
        }

        $projetos = $query->orderBy('created_at', 'desc')->get(); // Ordena por padrão
        $filtros = $request->except(['_token']); // Pega todos os filtros, exceto o token CSRF
        $usuarioLogado = auth()->user()->name; // Nome do usuário que gerou o relatório

        // Gera o PDF passando os dados para a view 'pdf.projetos-relatorio' (ou nome similar)
        $pdf = Pdf::loadView('pdf.projetos-relatorio', [ // Sugestão de nome para a view do relatório
            'projetos' => $projetos,
            'filtros' => $filtros,
            'usuarioLogado' => $usuarioLogado,
            'dataGeracao' => now()->format('d/m/Y H:i:s')
        ]);
        // Opções de PDF (tamanho, orientação)
        $pdf->setPaper('a4', 'portrait'); // Ex: Paisagem para tabelas largas

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
        // Carrega o projeto com todas as suas relações necessárias para o PDF
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'user', 'rejeicoes'])->findOrFail($id);

        // Define o locale para pt_BR para formatação de datas no PDF, se ainda não global
        // Carbon::setLocale('pt_BR'); // Melhor se configurado globalmente no AppServiceProvider

        // Gera o PDF usando a view 'projetos.pdf' e os dados do projeto
        $pdf = Pdf::loadView('projetos.pdf', compact('projeto'));

        // Define um nome de arquivo para o download
        $nomeArquivo = "proposta_extensionista_{$projeto->id}.pdf";
        // Sanitiza o nome do arquivo (opcional, mas bom para remover caracteres problemáticos)
        $nomeArquivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeArquivo);


        return $pdf->download($nomeArquivo);
    }
}