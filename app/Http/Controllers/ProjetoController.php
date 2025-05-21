<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use App\Models\Projeto;
use App\Models\Aluno;
use App\Models\Professor;
use App\Models\Atividade;
use App\Models\Cronograma;
use App\Http\Requests\StoreProjetoRequest;
use App\Http\Requests\UpdateProjetoRequest;
use App\Models\Rejeicao;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class ProjetoController extends Controller
{


    public function index()
    {
        $query = Projeto::with(['atividades', 'user', 'professores']);
    
        $user = auth()->user();
    
        if ($user->role === 'aluno') {
            $query->where('user_id', $user->id);
        }
    
        if ($user->role === 'professor') {
            $query->whereHas('professores', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
    
        if (in_array($user->role, ['napex', 'coordenador'])) {
            // Se o status foi enviado manualmente via URL
            if (request()->filled('status')) {
                // Só aceita 'entregue' ou 'aprovado', senão ignora
                if (in_array(request('status'), ['entregue', 'aprovado'])) {
                    $query->where('status', request('status'));
                } else {
                    // Ignora status inválido para napex/coord
                    $query->whereIn('status', ['entregue', 'aprovado']);
                }
            } else {
                // Sem status enviado: mostra só os permitidos
                $query->whereIn('status', ['entregue', 'aprovado']);
            }
        }


    
        if (request('cadastrado_por')) {
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
    
        if (request('status')) {
            $query->where('status', request('status'));
        }
    
        if (request('aprovado_napex') === 'sim') {
            $query->where('aprovado_napex', 'sim');
        }
    
        if (request('aprovado_coordenador') === 'sim') {
            $query->where('aprovado_coordenador', 'sim');
        }
    
        if (request('aprovacao_final') === 'sim') {
            $query->where('aprovado_napex', 'sim')->where('aprovado_coordenador', 'sim');
        }
    
        // filtro de intervalo para data_inicio
        if (request('data_inicio_de') && request('data_inicio_ate')) {
            $query->whereBetween('data_inicio', [request('data_inicio_de'), request('data_inicio_ate')]);
        } elseif (request('data_inicio_de')) {
            $query->whereDate('data_inicio', '>=', request('data_inicio_de'));
        } elseif (request('data_inicio_ate')) {
            $query->whereDate('data_inicio', '<=', request('data_inicio_ate'));
        }
    
        //filtro de intervalo para data_fim
        if (request('data_fim_de') && request('data_fim_ate')) {
            $query->whereBetween('data_fim', [request('data_fim_de'), request('data_fim_ate')]);
        } elseif (request('data_fim_de')) {
            $query->whereDate('data_fim', '>=', request('data_fim_de'));
        } elseif (request('data_fim_ate')) {
            $query->whereDate('data_fim', '<=', request('data_fim_ate'));
        }
        
        // Ordenação padrão: mais novos primeiro
        $ordenar = request('ordenar');
        if ($ordenar == 'data_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            // Padrão (inclusive se for 'data_desc' ou nulo): mais novos no topo
            $query->orderBy('created_at', 'desc');
        }


        //filtro das cargas
        $query->whereHas('atividades', function ($q) {
            $q->selectRaw('projeto_id, SUM(carga_horaria) as soma')
            ->groupBy('projeto_id')
            ->havingRaw('? IS NULL OR SUM(carga_horaria) >= ?', [request('carga_min'), request('carga_min')])
            ->havingRaw('? IS NULL OR SUM(carga_horaria) <= ?', [request('carga_max'), request('carga_max')]);
        });
        //com paginação
        $projetos = $query->paginate(10)->appends(request()->query());


    
        return view('projetos.index', compact('projetos'));
    }
    
    

    

    public function create()
    {
        if (auth()->user()->role !== 'aluno') {
            abort(403, 'Apenas alunos podem criar projetos.');
        }

        $professores = User::where('role', 'professor')->get();
        return view('projetos.create', compact('professores'));
    }

    

    public function store(StoreProjetoRequest $request)
    {
        if (auth()->user()->role !== 'aluno') {
            abort(403, 'Apenas alunos podem cadastrar projetos.');
        }
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
        $data['professor_id'] = $request->input('professor_id'); // OBS: pode ser removido se não estiver em uso

        $projeto = Projeto::create($data);

        if ($request->has('alunos')) {
            foreach ($request->alunos as $aluno) {
                $projeto->alunos()->create($aluno);
            }
        }

        if ($request->has('professores')) {
            $professorIds = [];

            foreach ($request->professores as $professorData) {
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
            foreach ($request->atividades as $atividade) {
                $projeto->atividades()->create($atividade);
            }
        }

        if ($request->has('cronograma')) {
            foreach ($request->cronograma as $cronograma) {
                $projeto->cronogramas()->create($cronograma);
            }
        }

        return redirect()->route('projetos.index')->with('success', 'Projeto salvo com sucesso!');
    }

    
    

    public function show($id)
    {
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas', 'rejeicoes'])->findOrFail($id);
        return view('projetos.show', compact('projeto'));
    }

    public function edit($id)
    {
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas'])->findOrFail($id);
        $user = auth()->user();
        $userRole = $user->role;

        // Bloqueia edição se NAPEx ou Coordenador já aprovou
        if ($projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto já possui aprovação e não pode mais ser editado.');
        }

        // BUSCA OS PROFESSORES CADASTRADOS
        $professores = User::where('role', 'professor')->get();

        if ($userRole === 'aluno' && $projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
        }

        if ($userRole === 'professor') {
            $isProfessorLinked = $projeto->professores->contains('user_id', $user->id);

            if (!$isProfessorLinked) {
                return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para editar este projeto.');
            }

            if ($projeto->status === 'entregue') {
                return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
            }
        }

        return view('projetos.edit', compact('projeto', 'professores'));
    }

    
    
    

    public function update(UpdateProjetoRequest $request, $id)
    {
        $projeto = Projeto::with('professores')->findOrFail($id);
        $user = auth()->user();
        $userRole = $user->role;
    
        if ($userRole === 'aluno' && $projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
        }
    
        if ($userRole === 'professor') {
            $isProfessorLinked = $projeto->professores->contains('user_id', $user->id);
            if (!$isProfessorLinked) {
                return redirect()->route('projetos.index')->with('error', 'Você não tem permissão para atualizar este projeto.');
            }
            if ($projeto->status === 'entregue') {
                return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
            }
        }
    
        $data = $request->validated();
        foreach (['data_recebimento_napex', 'data_encaminhamento_parecer', 'data_parecer_coordenador'] as $campo) {
            if (!empty($data[$campo]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$campo])) {
                unset($data[$campo]);
            }
        }
        
        $projeto->update($data);
    
        // Atualizar alunos
        $projeto->alunos()->delete();
        if ($request->has('alunos')) {
            foreach ($request->alunos as $aluno) {
                $projeto->alunos()->create($aluno);
            }
        }
    
        // Atualizar professores
        $projeto->professores()->delete();
        if ($request->has('professores')) {
            $professorIds = [];
    
            foreach ($request->professores as $professorData) {
                if (in_array($professorData['id'], $professorIds)) {
                    return redirect()->back()->with('error', 'Você tentou adicionar o mesmo professor mais de uma vez.');
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
            foreach ($request->atividades as $atividade) {
                $projeto->atividades()->create($atividade);
            }
        }
    
        // Atualizar cronograma
        $projeto->cronogramas()->delete();
        if ($request->has('cronograma')) {
            foreach ($request->cronograma as $cronograma) {
                $projeto->cronogramas()->create($cronograma);
            }
        }
    
        return redirect()->route('projetos.show', $projeto->id)->with('success', 'Projeto atualizado com sucesso!');

    }
        
    
        

    public function avaliarNapex(Request $request, $id)
    {
        if (auth()->user()->role !== 'napex') {
            abort(403, 'Apenas NAPEx pode avaliar nesta etapa.');
        }

    
        $projeto = Projeto::findOrFail($id);

        if ($request->input('aprovado_napex') === 'nao') {
            $this->registrarRejeicao($projeto, $request->input('motivo_napex'), 'napex');
            $this->limparAprovacoes($projeto);
    
            return redirect()->route('projetos.index')->with('success', 'Projeto reprovado pelo NAPEx e devolvido ao aluno.');
        }
    
        $napexData = $request->only([
            'numero_projeto',
            'data_recebimento_napex',
            'data_encaminhamento_parecer',
            'aprovado_napex',
            'motivo_napex'
        ]);
    
        try {
            if (!empty($napexData['data_recebimento_napex']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $napexData['data_recebimento_napex'])) {
                return redirect()->back()->with('error', 'A data de recebimento está inválida. Use o formato YYYY-MM-DD.');
            }
    
            if (!empty($napexData['data_encaminhamento_parecer']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $napexData['data_encaminhamento_parecer'])) {
                return redirect()->back()->with('error', 'A data de encaminhamento está inválida. Use o formato YYYY-MM-DD.');
            }
    
            $projeto->update($napexData);
            
            //atualiza status p/ aprovado caso napex e coord aprove
            if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
                $projeto->status = 'aprovado';
                $projeto->save();
            }   

    
            return redirect()->route('projetos.show', $id)->with('success', 'Parecer do NAPEx salvo com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao salvar no banco: verifique os dados e tente novamente.');
        }
    }
    
    public function avaliarCoordenador(Request $request, $id)
    {
        if (auth()->user()->role !== 'coordenador') {
            abort(403, 'Apenas Coordenador pode avaliar nesta etapa.');
        }

        $projeto = Projeto::findOrFail($id);

        if ($request->input('aprovado_coordenador') === 'nao') {
            $this->registrarRejeicao($projeto, $request->input('motivo_coordenador'), 'coordenador');
            $this->limparAprovacoes($projeto);
    
            return redirect()->route('projetos.index')->with('success', 'Projeto reprovado pela Coordenação e devolvido ao aluno.');
        }

        $coordData = $request->only([
            'aprovado_coordenador',
            'motivo_coordenador',
            'data_parecer_coordenador'
        ]);

        try {
            if (!empty($coordData['data_parecer_coordenador'])) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $coordData['data_parecer_coordenador'])) {
                    return redirect()->back()->with('error', 'A data informada está inválida. Use o formato YYYY-MM-DD.');
                }
            }

            $projeto->update($coordData);

            if ($projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim') {
                $projeto->status = 'aprovado';
                $projeto->save();
            }


            return redirect()->route('projetos.show', $id)->with('success', 'Parecer do Coordenador salvo com sucesso.');

        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Erro ao salvar no banco: verifique os dados e tente novamente.');
        }
    }
    
    private function limparAprovacoes($projeto)
    {
        $projeto->update([
            'numero_projeto' => null,
            'data_recebimento_napex' => null,
            'data_encaminhamento_parecer' => null,
            'aprovado_napex' => 'pendente',
            'motivo_napex' => null,
            'aprovado_coordenador' => 'pendente',
            'motivo_coordenador' => null,
            'data_parecer_coordenador' => null,
            'status' => 'editando',
        ]);
    }


    

    public function destroy($id)
    {
        $projeto = Projeto::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'aluno') {
            abort(403, 'Apenas alunos podem excluir projetos.');
        }

        $projeto->delete();

        return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');
    }

    

    public function enviarProjeto($id)
    {
        $projeto = Projeto::findOrFail($id);

        if ($projeto->status === 'editando') {
            $projeto->status = 'entregue';
            $projeto->save();
        }

        return redirect()->route('projetos.index')->with('success', 'Projeto enviado com sucesso!');
    }

    public function voltarParaEdicao($id)
    {
        $projeto = Projeto::findOrFail($id);
    
        // Impede retorno à edição se já tiver sido aprovado por NAPEx ou Coordenação
        if ($projeto->status === 'aprovado' || $projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
            return redirect()->route('projetos.index')->with('error', 'Não é possível voltar para edição após aprovação.');
        }

    
        $projeto->status = 'editando';
        $projeto->save();
    
        return redirect()->route('projetos.index')->with('success', 'Projeto liberado para edição novamente.');
    }
    

    private function registrarRejeicao($projeto, $motivo, $autor)
    {
        Rejeicao::create([
            'projeto_id' => $projeto->id,
            'motivo' => $motivo,
            'data_rejeicao' => now(),
            'autor' => $autor,
        ]);
    }
    
    //Exportar PDF
    public function exportarPdf(Request $request)
    {
        if (!in_array(auth()->user()->role, ['napex', 'coordenador'])) {
            abort(403);
        }

        $query = Projeto::query()->with(['user', 'atividades']);

        // Somente status 'entregue' ou 'aprovado'
        $query->whereIn('status', ['entregue', 'aprovado']);

        // 🔍 Filtros
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
        }

        if ($request->filled('data_fim_de') && $request->filled('data_fim_ate')) {
            $query->whereBetween('data_fim', [$request->data_fim_de, $request->data_fim_ate]);
        }

        // ✅ Corrigido: filtro de carga horária pelas atividades
        if ($request->filled('carga_min') || $request->filled('carga_max')) {
            $query->whereHas('atividades', function ($q) use ($request) {
                $q->selectRaw('projeto_id, SUM(carga_horaria) as soma')
                ->groupBy('projeto_id');

                if ($request->filled('carga_min')) {
                    $q->havingRaw('SUM(carga_horaria) >= ?', [$request->carga_min]);
                }

                if ($request->filled('carga_max')) {
                    $q->havingRaw('SUM(carga_horaria) <= ?', [$request->carga_max]);
                }
            });
        }

        if ($request->filled('status') && $request->status !== '--') {
            $query->where('status', $request->status);
        }

        if ($request->filled('aprovado_napex') && $request->aprovado_napex !== '--') {
            $query->where('aprovado_napex', $request->aprovado_napex);
        }

        if ($request->filled('aprovado_coordenador') && $request->aprovado_coordenador !== '--') {
            $query->where('aprovado_coordenador', $request->aprovado_coordenador);
        }

        $projetos = $query->get();
        $filtros = $request->all();
        $usuario = auth()->user()->name;

        $pdf = Pdf::loadView('pdf.projetos', [
            'projetos' => $projetos,
            'filtros' => $filtros,
            'usuario' => $usuario
        ]);

        return $pdf->download('relatorio_projetos.pdf');
    }

    // Função dpf proposta
    

    public function gerarPdf($id)
    {
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas'])->findOrFail($id);

        $pdf = Pdf::loadView('projetos.pdf', compact('projeto'));

        return $pdf->download("proposta_projeto_{$projeto->id}.pdf");
    }


    
}
