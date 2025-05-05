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

class ProjetoController extends Controller
{
    public function downloadArquivo($id)
    {
        $projeto = Projeto::findOrFail($id);

        if (!$projeto->arquivo || !file_exists(public_path($projeto->arquivo))) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Response::download(public_path($projeto->arquivo));
    }

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
            $query->where('status', 'entregue');
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
    
        if (request('data_inicio')) {
            $query->whereDate('data_inicio', '>=', request('data_inicio'));
        }
    
        if (request('data_fim')) {
            $query->whereDate('data_fim', '<=', request('data_fim'));
        }
    
        $projetos = $query->get();
    
        $projetos = $projetos->filter(function ($projeto) {
            $carga = $projeto->atividades->sum('carga_horaria');
            $min = request('carga_min');
            $max = request('carga_max');
            return (!$min || $carga >= $min) && (!$max || $carga <= $max);
        });
    
        return view('projetos.index', compact('projetos'));
    }
    

    

    public function create()
    {
        $professores = User::where('role', 'professor')->get();
        return view('projetos.create', compact('professores'));
    }
    

    public function store(StoreProjetoRequest $request)
    {
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
    
        $data['professor_id'] = $request->input('professor_id');
    
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
                    return redirect()->back()->with('error', 'Você tentou adicionar o mesmo professor mais de uma vez.');
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
    
        // BUSCA OS PROFESSORES CADASTRADOS
        $professores = User::where('role', 'professor')->get();
    
        if ($userRole === 'aluno' && $projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
        }
    
        if ($userRole === 'professor') {
            // VERIFICA SE O PROFESSOR LOGADO ESTÁ NA LISTA DE PROFESSORES RELACIONADOS
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
    
        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
    }
        
    
        

    public function avaliarNapex(Request $request, $id)
    {
        $projeto = Projeto::findOrFail($id);
    
        if ($request->input('aprovado_napex') === 'nao') {
            $this->registrarRejeicao($projeto, $request->input('motivo_napex'), 'napex');
    
            $projeto->update([
                'numero_projeto' => null,
                'data_recebimento_napex' => null,
                'data_encaminhamento_parecer' => null,
                'aprovado_napex' => null,
                'motivo_napex' => null,
                'aprovado_coordenador' => null,
                'motivo_coordenador' => null,
                'data_parecer_coordenador' => null,
                'status' => 'editando',
            ]);
    
            return redirect()->route('projetos.index')->with('success', 'Projeto reprovado pelo NAPEx e devolvido ao aluno.');
        }
    
        $projeto->update($request->only([
            'numero_projeto',
            'data_recebimento_napex',
            'data_encaminhamento_parecer',
            'aprovado_napex',
            'motivo_napex'
        ]));
    
        return redirect()->route('projetos.show', $id)->with('success', 'Parecer do NAPEx salvo com sucesso.');
    }
    

    public function avaliarCoordenador(Request $request, $id)
    {
        $projeto = Projeto::findOrFail($id);
    
        if ($request->input('aprovado_coordenador') === 'nao') {
            $this->registrarRejeicao($projeto, $request->input('motivo_coordenador'), 'coordenador');
    
            $projeto->update([
                'numero_projeto' => null,
                'data_recebimento_napex' => null,
                'data_encaminhamento_parecer' => null,
                'aprovado_napex' => null,
                'motivo_napex' => null,
                'aprovado_coordenador' => null,
                'motivo_coordenador' => null,
                'data_parecer_coordenador' => null,
                'status' => 'editando',
            ]);
    
            return redirect()->route('projetos.index')->with('success', 'Projeto reprovado pela Coordenação e devolvido ao aluno.');
        }
    
        $projeto->update($request->only([
            'aprovado_coordenador',
            'motivo_coordenador',
            'data_parecer_coordenador'
        ]));
    
        return redirect()->route('projetos.show', $id)->with('success', 'Parecer do Coordenador salvo com sucesso.');
    }
    

    public function destroy($id)
    {
        $projeto = Projeto::findOrFail($id);
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
        if ($projeto->aprovado_napex === 'sim' || $projeto->aprovado_coordenador === 'sim') {
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
    
}
