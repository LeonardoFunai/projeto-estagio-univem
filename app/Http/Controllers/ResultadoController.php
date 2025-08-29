<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResultadoController extends Controller
{
    /**
     * Mostra o formulário para criar um novo relatório de resultados.
     */
    public function create(Projeto $projeto)
    {
        // ... (código do create continua o mesmo)
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
    public function store(Request $request, Projeto $projeto)
    {
        // CORREÇÃO AQUI: Lista de validação completa
        $validatedData = $request->validate([
            'atividades_desenvolvidas' => 'required|string',
            'comunidade_externa' => 'nullable|string',
            'parceiro_organizacao' => 'nullable|string|max:255',
            'parceiro_endereco' => 'nullable|string|max:255',
            'parceiro_cnpj' => 'nullable|string|max:20',
            'parceiro_responsavel' => 'nullable|string|max:255',
            'parceiro_tipo_participacao' => 'nullable|string|max:255',
            'anexos_descricao' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:10240',
        ]);

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
        
        $resultado->load('projeto.user');
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
        // ... (código do edit continua o mesmo)
        $projeto = $resultado->projeto;
        if (auth()->user()->id !== $projeto->user_id || !in_array($resultado->status, ['rascunho', 'reprovado'])) {
            abort(403, 'Acesso não autorizado ou ação não permitida no status atual.');
        }
        return view('resultados.edit', compact('resultado'));
    }




    public function update(Request $request, Resultado $resultado)
    {
        // A lógica de autorização e validação continuam as mesmas...
        $projeto = $resultado->projeto;
        if (auth()->user()->id !== $projeto->user_id || !in_array($resultado->status, ['rascunho', 'reprovado'])) {
            abort(403, 'Acesso não autorizado.');
        }

            $validatedData = $request->validate([
                'atividades_desenvolvidas' => 'required|string',
                'comunidade_externa' => 'nullable|string',
                'parceiro_organizacao' => 'nullable|string|max:255',
                'parceiro_endereco' => 'nullable|string|max:255',
                'parceiro_cnpj' => 'nullable|string|max:20',
                'parceiro_responsavel' => 'nullable|string|max:255',
                'parceiro_tipo_participacao' => 'nullable|string|max:255',
                'anexos_descricao' => 'nullable|string',
                'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'videos.*' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:10240',
            ]);
        
        // Ação principal: Apenas atualiza os dados. Não mexe mais no status.
        $resultado->update($validatedData);

        return redirect()->route('resultados.show', $resultado)->with('success', 'Relatório de Resultados atualizado com sucesso!');
    }



    /**
     * Submete o resultado para avaliação.
     */
    public function enviar(Resultado $resultado)
    {
        // Segurança: Garante que só o dono pode enviar.
        if (auth()->user()->id !== $resultado->projeto->user_id) {
            abort(403);
        }
        
        // Altera o status para 'enviado' e limpa avaliações antigas (caso seja um reenvio)
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
        // Segurança: Garante que só o dono pode realizar a ação.
        if (auth()->user()->id !== $resultado->projeto->user_id) {
            abort(403);
        }

        // VERIFICAÇÃO ADICIONADA: A ação só é permitida se o status for 'enviado'.
        if ($resultado->status !== 'enviado') {
            return back()->with('error', 'Esta ação não é permitida no status atual do relatório.');
        }

        // REGRA DE BLOQUEIO: Verifica se já houve alguma aprovação.
        if ($resultado->aprovado_napex === 'sim' || $resultado->aprovado_coordenador === 'sim') {
            return back()->with('error', 'Não é possível voltar para edição pois o relatório já foi avaliado.');
        }

        $resultado->status = 'rascunho';
        $resultado->save();

        return redirect()->route('resultados.edit', $resultado)->with('success', 'O relatório de resultados voltou para o modo de edição.');
    }



    /**
     * Salva o parecer de um avaliador (NAPEX ou Coordenador).
     */
    public function avaliar(Request $request, Resultado $resultado)
    {
        // 1. Validação: Garante que o parecer e a decisão foram enviados.
        $request->validate([
            'parecer' => 'required|string|min:10',
            'aprovacao' => 'required|in:sim,nao',
        ]);

        $user = auth()->user();
        $role = $user->role;

        // 2. Autorização e Lógica de salvamento:
        // Verifica o perfil do usuário e salva o parecer no campo correto.
        if ($role === 'napex') {
            if ($resultado->aprovado_napex !== 'pendente') {
                return back()->with('error', 'Este resultado já foi avaliado pelo NAPEX.');
            }
            $resultado->parecer_napex = $request->parecer;
            $resultado->aprovado_napex = $request->aprovacao;
        } elseif ($role === 'coordenador') {
            if ($resultado->aprovado_coordenador !== 'pendente') {
                return back()->with('error', 'Este resultado já foi avaliado pela Coordenação.');
            }
            $resultado->parecer_coordenador = $request->parecer;
            $resultado->aprovado_coordenador = $request->aprovacao;
        } else {
            // Se um usuário não autorizado tentar acessar, retorna um erro.
            return back()->with('error', 'Você não tem permissão para avaliar.');
        }

        // 3. Lógica de Atualização de Status Geral:
        // Após salvar o parecer individual, o sistema verifica o estado geral da avaliação.
        $aprovadoNapex = $resultado->aprovado_napex;
        $aprovadoCoord = $resultado->aprovado_coordenador;

        if ($aprovadoNapex === 'nao' || $aprovadoCoord === 'nao') {
            // Se QUALQUER UM reprovar, o status do resultado vira 'reprovado'
            // e o aluno será notificado para corrigir.
            $resultado->status = 'reprovado';

        } elseif ($aprovadoNapex === 'sim' && $aprovadoCoord === 'sim') {
            // Se AMBOS aprovarem, o status do resultado vira 'aprovado'
            $resultado->status = 'aprovado';

            // E a ETAPA do projeto pai finalmente avança para 'Concluído'
            $projeto = $resultado->projeto;
            $projeto->etapa = 'Concluído';
            $projeto->save();
        }
        // Se apenas um aprovou e o outro está pendente, o status do resultado continua 'enviado'.

        $resultado->save();

        // 4. Redirecionamento: Volta para a tela de visualização com mensagem de sucesso.
        return redirect()->route('resultados.show', $resultado)->with('success', 'Parecer enviado com sucesso!');
    }
}