<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResultadoRequest; 
use App\Models\RejeicaoResultado;

class ResultadoController extends Controller
{
    /**
     * Mostra o formulário para criar um novo relatório de resultados.
     */
    public function create(Projeto $projeto)
    {
        
        if (auth()->user()->id !== $projeto->user_id) {
            abort(403, 'Acesso não autorizado.');
        }
        if ($projeto->etapa !== 'Resultado') {
            return redirect()->route('projetos.index')->with('error', 'Só é possível adicionar resultados a projetos na Etapa de Resultado.');
        }
        if ($projeto->resultado) {
            return redirect()->route('resultados.edit', $projeto->resultado)->with('info', 'Este projeto já possui um relatório de resultados. Você pode editá-lo aqui.');
        }
        return view('resultados.create', compact('projeto'));
    }

    /**
     * Salva o novo relatório de resultados no banco de dados.
     */
    public function store(StoreResultadoRequest $request, Projeto $projeto)
    {
        
        $validatedData = $request->validated();

        $dadosParaSalvar = $validatedData;
        $dadosParaSalvar['projeto_id'] = $projeto->id;
        $dadosParaSalvar['status'] = 'rascunho';

        // Lógica de upload de arquivos...
        if ($request->hasFile('fotos')) {
            // ...
        }
        
        Resultado::create($dadosParaSalvar);

        return redirect()->route('projetos.index')->with('success', 'Rascunho do Relatório de Resultados salvo com sucesso!');
    }

    /**
     * Mostra os detalhes de um resultado.
     */
    public function show(Resultado $resultado)
    {
        $user = auth()->user();
        if (in_array($user->role, ['napex', 'coordenador'])) {
            // Se o avaliador tentar ver um resultado que não está na sua fila (ex: rascunho, reprovado), bloqueie.
            if (!in_array($resultado->status, ['enviado', 'aprovado'])) {
                abort(403, 'Acesso não autorizado para este status de resultado.');
            }
        }
        $resultado->load('projeto.user', 'rejeicoes.user');
        $response = response(view('resultados.show', compact('resultado')));
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
        $projeto = $resultado->projeto;
        if (auth()->user()->id !== $projeto->user_id || !in_array($resultado->status, ['rascunho', 'reprovado'])) {
            abort(403, 'Acesso não autorizado ou ação não permitida no status atual.');
        }
        return view('resultados.edit', compact('resultado'));
    }

    /**
     * Atualiza um resultado existente no banco de dados.
     */
    public function update(StoreResultadoRequest $request, Resultado $resultado)
    {
        
        $validatedData = $request->validated();
        
        $resultado->update($validatedData);

        if ($resultado->status === 'reprovado') {
            $resultado->status = 'enviado';
            $resultado->aprovado_napex = 'pendente';
            $resultado->parecer_napex = null;
            $resultado->aprovado_coordenador = 'pendente';
            $resultado->parecer_coordenador = null;
            $resultado->save();
        }
    
        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados atualizado com sucesso!');
    }

    /**
     * Submete o resultado para avaliação.
     */
    public function enviar(Resultado $resultado)
    {
        if (auth()->user()->id !== $resultado->projeto->user_id) {
            abort(403);
        }
        
        $resultado->status = 'enviado';
        $resultado->aprovado_napex = 'pendente';
        $resultado->parecer_napex = null;
        $resultado->aprovado_coordenador = 'pendente';
        $resultado->parecer_coordenador = null;
        $resultado->save();

        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados enviado para avaliação!');
    }

    /**
     * Retorna um resultado enviado de volta para o status de rascunho.
     */
    public function voltarParaRascunho(Resultado $resultado)
    {
        if (auth()->user()->id !== $resultado->projeto->user_id) {
            abort(403);
        }
        if ($resultado->status !== 'enviado') {
            return back()->with('error', 'Esta ação não é permitida no status atual do relatório.');
        }
        if ($resultado->aprovado_napex === 'sim' || $resultado->aprovado_coordenador === 'sim') {
            return back()->with('error', 'Não é possível voltar para edição pois o relatório já foi avaliado.');
        }

        $resultado->status = 'rascunho';
        $resultado->save();

        return redirect()->route('resultados.edit', $resultado)->with('success', 'O relatório de resultados voltou para o modo de edição.');
    }

    

    /**
     * Salva ou atualiza o parecer de um avaliador (NAPEX ou Coordenador).
     */
    public function avaliar(Request $request, Resultado $resultado)
    {



        if (in_array($resultado->status, ['aprovado', 'reprovado'])) {
            return back()->with('error', 'A avaliação para este relatório já foi finalizada.');
        }

        $user = auth()->user();
        $role = $user->role;

      
        if ($role === 'napex') {
            $resultado->parecer_napex = $request->parecer;
            $resultado->aprovado_napex = $request->aprovacao;
        } elseif ($role === 'coordenador') {
            $resultado->parecer_coordenador = $request->parecer;
            $resultado->aprovado_coordenador = $request->aprovacao;
        } else {
            return back()->with('error', 'Você não tem permissão para avaliar.');
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
        }
        
        $resultado->save();

        return redirect()->route('resultados.show', $resultado)->with('success', 'Parecer salvo com sucesso!');
    }
}