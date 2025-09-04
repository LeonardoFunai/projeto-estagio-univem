<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResultadoRequest;
use App\Models\RejeicaoResultado;
use App\Models\Anexo;

class ResultadoController extends Controller
{
    /**
     * Mostra o formulário para criar um novo relatório de resultados.
     */
    public function create(Projeto $projeto)
    {
        // Policy: Verifica se o usuário logado pode criar um resultado para ESTE projeto.
        $this->authorize('create', [Resultado::class, $projeto]);

        // Lógica de negócio (continua no controller)
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
        $this->authorize('create', [Resultado::class, $projeto]);

        $validatedData = $request->validated();

        // Monta o array de dados para salvar, garantindo que todos os campos obrigatórios estejam presentes.
        $resultado = Resultado::create([
            'projeto_id' => $projeto->id,
            'status' => 'enviado',
            'atividades_desenvolvidas' => $validatedData['atividades_desenvolvidas'],
            'anexos_descricao' => $validatedData['anexos_descricao'] ?? null,
            'parceiro_organizacao' => $validatedData['parceiro_organizacao'] ?? null,
            'parceiro_responsavel' => $validatedData['parceiro_responsavel'] ?? null,
            'parceiro_endereco' => $validatedData['parceiro_endereco'] ?? null,
            'parceiro_cnpj' => $validatedData['parceiro_cnpj'] ?? null,
            'parceiro_tipo_participacao' => $validatedData['parceiro_tipo_participacao'] ?? null,
            'comunidade_externa' => $validatedData['comunidade_externa'] ?? null,
        ]);

        // A lógica de upload de anexos continua a mesma e está correta.
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $arquivo) {
                $caminho = $arquivo->store('resultados/' . $resultado->id, 'public');
                Anexo::create([
                    'resultado_id' => $resultado->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'path' => $caminho,
                    'mime_type' => $arquivo->getMimeType(),
                ]);
            }
        }

        $projeto->update(['etapa' => 'Resultado']);

        return redirect()->route('projetos.index')->with('success', 'Relatório de Resultados enviado com sucesso!');
}

    /**
     * Mostra os detalhes de um resultado.
     */
    public function show(Resultado $resultado)
    {
        // Policy: Verifica se o usuário pode ver este resultado.
        $this->authorize('view', $resultado);

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
        // Policy: Verifica se o usuário pode atualizar (editar) este resultado.
        $this->authorize('update', $resultado);

        return view('resultados.edit', compact('resultado'));
    }

    /**
     * Atualiza um resultado existente no banco de dados.
     */
    public function update(StoreResultadoRequest $request, Resultado $resultado)
    {
        // 1. Autorização (seu código original, mantido)
        $this->authorize('update', $resultado);

        // 2. Pega os dados validados do formulário
        $validatedData = $request->validated();

        // 3. GERENCIAR EXCLUSÃO DE ANEXOS MARCADOS
        if ($request->has('anexos_a_deletar')) {
            // Encontra os anexos pelos IDs enviados no formulário
            $anexosParaDeletar = Anexo::whereIn('id', $request->anexos_a_deletar)->get();
            
            foreach ($anexosParaDeletar as $anexo) {
                // Apaga o arquivo físico do disco
                Storage::disk('public')->delete($anexo->path);
                // Deleta o registro do banco de dados
                $anexo->delete();
            }
        }

        // 4. GERENCIAR UPLOAD DE NOVOS ANEXOS
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $arquivo) {
                // Salva o novo arquivo na pasta correta
                $caminho = $arquivo->store('resultados/' . $resultado->id, 'public');
                // Cria o registro do novo anexo no banco
                Anexo::create([
                    'resultado_id' => $resultado->id,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'path' => $caminho,
                    'mime_type' => $arquivo->getMimeType(),
                ]);
            }
        }

        // 5. ATUALIZAR OS DADOS DO RELATÓRIO
        // Primeiro, atualiza os campos de texto que foram validados
        $resultado->update($validatedData);

        // 6. APLICAR A LÓGICA DE REENVIO (seu código original, mantido e otimizado)
        // Se o relatório estava reprovado, ele é resetado para uma nova avaliação.
        if ($resultado->status === 'reprovado') {
            $resultado->update([
                'status' => 'enviado',
                'aprovado_napex' => 'pendente',
                'parecer_napex' => null,
                'aprovado_coordenador' => 'pendente',
                'parecer_coordenador' => null,
            ]);
        }

        // 7. Redirecionar para a página de visualização com mensagem de sucesso
        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados atualizado com sucesso!');
    }

    /**
     * Submete o resultado para avaliação.
     */
    public function enviar(Resultado $resultado)
    {
        // Policy: Verifica se o usuário tem permissão para enviar para avaliação.
        $this->authorize('sendForEvaluation', $resultado);

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
        // Policy: Verifica se o usuário tem permissão para reverter para rascunho.
        $this->authorize('revertToDraft', $resultado);

        $resultado->status = 'rascunho';
        $resultado->save();

        return redirect()->route('resultados.edit', $resultado)->with('success', 'O relatório de resultados voltou para o modo de edição.');
    }

    /**
     * Salva ou atualiza o parecer de um avaliador (NAPEX ou Coordenador).
     */
    public function avaliar(Request $request, Resultado $resultado)
    {
        // Policy: Verifica se o usuário pode avaliar.
        $this->authorize('evaluate', $resultado);

        $user = auth()->user();
        $role = $user->role;

        
        if ($role === 'napex') {
            $resultado->parecer_napex = $request->parecer;
            $resultado->aprovado_napex = $request->aprovacao;
        } elseif ($role === 'coordenador') {
            $resultado->parecer_coordenador = $request->parecer;
            $resultado->aprovado_coordenador = $request->aprovacao;
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

        return redirect()->route('projetos.index', $resultado)->with('success', 'Parecer salvo com sucesso!');
    }
}