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
        $query = Projeto::with('atividades');

        if (request('titulo')) {
            $query->where('titulo', 'like', '%' . request('titulo') . '%');
        }

        if (request('periodo')) {
            $query->where('periodo', 'like', '%' . request('periodo') . '%');
        }

        if (request('status')) {
            $query->where('status', request('status'));
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
        return view('projetos.create');
    }

    public function store(StoreProjetoRequest $request)
    {
        $data = $request->validated();

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

        if ($projeto->status === 'entregue') {
            return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e n\u00e3o pode mais ser editado.');
        }

        return view('projetos.edit', compact('projeto'));
    }

    public function update(UpdateProjetoRequest $request, $id)
{
    $projeto = Projeto::findOrFail($id);

    if ($projeto->status === 'entregue') {
        return redirect()->route('projetos.index')->with('error', 'Este projeto foi entregue e não pode mais ser editado.');
    }

    $data = $request->validated();

    // Atualiza o projeto
    $projeto->update($data);

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

    // 🔥 Aqui é o importante: Atualizando cronogramas sem apagar tudo
// Atualizando cronogramas sem apagar tudo
if ($request->has('cronograma')) {
    $idsExistentes = $projeto->cronogramas()->pluck('id')->toArray();
    $idsRecebidos = [];

    foreach ($request->cronograma as $item) {
        if (isset($item['id'])) {
            // Atualiza cronograma existente
            $cronograma = $projeto->cronogramas()->where('id', $item['id'])->first();
            if ($cronograma) {
                $cronograma->update([
                    'atividade' => $item['atividade'],
                    'mes' => $item['mes'],
                ]);
                $idsRecebidos[] = $item['id'];
            }
        } else {
            // Cria novo cronograma
            $projeto->cronogramas()->create([
                'atividade' => $item['atividade'],
                'mes' => $item['mes'],
            ]);
        }
    }

    // Deleta cronogramas que existiam mas o usuário removeu
    $idsParaDeletar = array_diff($idsExistentes, $idsRecebidos);
    if (count($idsParaDeletar) > 0) {
        $projeto->cronogramas()->whereIn('id', $idsParaDeletar)->delete();
    }
}


    return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
}

    

    public function destroy($id)
    {
        $projeto = Projeto::findOrFail($id);
        $projeto->delete();

        return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');
    }
}
