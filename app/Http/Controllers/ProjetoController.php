<?php

namespace App\Http\Controllers;

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
            abort(404, 'Arquivo n\u00e3o encontrado.');
        }

        return Response::download(public_path($projeto->arquivo));
    }

    public function index()
    {
        $query = Projeto::with(['atividades', 'user']);

        $user = auth()->user();

        // Se for aluno, mostra só os projetos dele
        if ($user->role === 'aluno') {
            $query->where('user_id', $user->id);
        }

        // Se for NAPEx ou Coordenador, mostra apenas projetos entregues
        if (in_array($user->role, ['napex', 'coordenador'])) {
            $query->where('status', 'entregue');
        }

        // Filtros
        if (request('titulo')) {
            $query->where('titulo', 'like', '%' . request('titulo') . '%');
        }

        if (request('periodo')) {
            $query->where('periodo', 'like', '%' . request('periodo') . '%');
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('data_inicio')) {
            $query->whereDate('data_inicio', '>=', request('data_inicio'));
        }

        if (request('data_fim')) {
            $query->whereDate('data_fim', '<=', request('data_fim'));
        }

        $projetos = $query->get();

        // Filtro de carga horária após carregar as atividades
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
        return view('projetos.create');
    }

    public function store(StoreProjetoRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 'editando';  // Forçar o status para "editando"

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

        // Vínculo do projeto ao usuário autenticado
        $data['user_id'] = auth()->id(); 

        $projeto = Projeto::create($data);

        if ($request->has('alunos')) {
            foreach ($request->alunos as $aluno) {
                $projeto->alunos()->create($aluno);
            }
        }

        if ($request->has('professores')) {
            foreach ($request->professores as $professor) {
                $projeto->professores()->create($professor);
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
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas'])->findOrFail($id);
        return view('projetos.show', compact('projeto'));
    }

    public function edit($id)
    {
        $projeto = Projeto::with(['alunos', 'professores', 'atividades', 'cronogramas'])->findOrFail($id);
    
        $userRole = auth()->user()->role;
    
        // Se for aluno e o projeto está entregue, bloqueia
        if ($userRole === 'aluno' && $projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
        }
    
        return view('projetos.edit', compact('projeto'));
    }
    

    public function update(UpdateProjetoRequest $request, $id)
    {
        $projeto = Projeto::findOrFail($id);
    
        $userRole = auth()->user()->role;

        // Aluno não pode editar se o projeto já foi entregue
        if ($userRole === 'aluno' && $projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
        }
          
    

        $data = $request->validated();
    
        // Atualiza dados normais do projeto
        $projeto->update($data);
    
        // Se for aluno ou napex, atualiza alunos, professores e atividades normalmente
        if (in_array($userRole, ['aluno', 'napex'])) {
    
            // Atualizando alunos
            $projeto->alunos()->delete();
            if ($request->has('alunos')) {
                foreach ($request->alunos as $aluno) {
                    $projeto->alunos()->create($aluno);
                }
            }
    
            // Atualizando professores
            $projeto->professores()->delete();
            if ($request->has('professores')) {
                foreach ($request->professores as $professor) {
                    $projeto->professores()->create($professor);
                }
            }
    
            // Atualizando atividades
            $projeto->atividades()->delete();
            if ($request->has('atividades')) {
                foreach ($request->atividades as $atividade) {
                    $projeto->atividades()->create($atividade);
                }
            }
    
            // Atualizando cronogramas
            if ($request->has('cronograma')) {
                $idsExistentes = $projeto->cronogramas()->pluck('id')->toArray();
                $idsRecebidos = [];
    
                foreach ($request->cronograma as $item) {
                    if (isset($item['id'])) {
                        $cronograma = $projeto->cronogramas()->where('id', $item['id'])->first();
                        if ($cronograma) {
                            $cronograma->update([
                                'atividade' => $item['atividade'],
                                'mes' => $item['mes'],
                            ]);
                            $idsRecebidos[] = $item['id'];
                        }
                    } else {
                        $projeto->cronogramas()->create([
                            'atividade' => $item['atividade'],
                            'mes' => $item['mes'],
                        ]);
                    }
                }
    
                $idsParaDeletar = array_diff($idsExistentes, $idsRecebidos);
                if (count($idsParaDeletar) > 0) {
                    $projeto->cronogramas()->whereIn('id', $idsParaDeletar)->delete();
                }
            }
        }
    
        // Se for coordenador, só atualiza o parecer dele (já atualizado no $data acima)
    
        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
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

        if ($projeto->status === 'entregue' && !$projeto->napex_aprovado && !$projeto->coordenacao_aprovado) {
            $projeto->status = 'editando';
            $projeto->save();
        }

        return redirect()->route('projetos.index')->with('success', 'Projeto liberado para edição novamente.');
    }

    public function darParecer(Request $request, $id)
    {
        $projeto = Projeto::findOrFail($id);

        $perfil = auth()->user()->role; // aluno, napex, coordenador

        if ($request->input('aprovado') === 'sim') {
            if ($perfil === 'napex') {
                $projeto->napex_aprovado = true;
            } elseif ($perfil === 'coordenador') {
                $projeto->coordenacao_aprovado = true;
            }

            // Se ambos aprovarem
            if ($projeto->napex_aprovado && $projeto->coordenacao_aprovado) {
                $projeto->status = 'aprovado';
            }

            $projeto->save();

            return redirect()->route('projetos.index')->with('success', 'Parecer registrado como aprovado.');
        } 
        else {
            $this->registrarRejeicao($projeto, $request->input('motivo'));

            $projeto->status = 'editando';
            $projeto->napex_aprovado = false;
            $projeto->coordenacao_aprovado = false;
            $projeto->save();

            return redirect()->route('projetos.index')->with('success', 'Projeto rejeitado e liberado para edição.');
        }
    }

    private function registrarRejeicao($projeto, $motivo)
    {
        Rejeicao::create([
            'projeto_id' => $projeto->id,
            'motivo' => $motivo,
            'data_rejeicao' => now(),
        ]);
    }
}
