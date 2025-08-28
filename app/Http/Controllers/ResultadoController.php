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
        // 1. Verificação de segurança: O usuário logado é o dono do projeto?
        if (auth()->user()->id !== $projeto->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // 2. Verificação de regra: O projeto está na etapa correta?
        if ($projeto->etapa !== 'Resultado') {
            return redirect()->route('projetos.index')->with('error', 'Só é possível adicionar resultados a projetos na Etapa de Resultado.');
        }

        // 3. Verificação de duplicidade: O projeto já tem um resultado?
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
        // Validação (pode ser movida para um FormRequest depois)
        $validatedData = $request->validate([
            'atividades_desenvolvidas' => 'required|string',
            // ... (outras regras de validação)
        ]);

        $dadosParaSalvar = $validatedData;
        $dadosParaSalvar['projeto_id'] = $projeto->id;
        // NOVO: Define o status inicial como 'rascunho'
        $dadosParaSalvar['status'] = 'rascunho';

        // Lógica de upload de arquivos (se houver)...
        
        Resultado::create($dadosParaSalvar);
        
        // ATENÇÃO: A etapa do projeto NÃO muda aqui. Ela já foi mudada para 'Resultado'
        // quando a proposta foi aprovada.

        return redirect()->route('projetos.index')->with('success', 'Rascunho do Relatório de Resultados salvo com sucesso!');
    }

    /**
     * Mostra os detalhes de um resultado (para alunos e avaliadores).
     */
    public function show(Resultado $resultado)
    {
        $resultado->load('projeto.user'); // Carrega as relações necessárias
        
        // A lógica de autorização (Policy) seria ideal aqui
        
        return view('resultados.show', compact('resultado'));
    }

    /**
     * Mostra o formulário para editar um resultado existente.
     */
    public function edit(Resultado $resultado)
    {
        $projeto = $resultado->projeto;

        // LÓGICA CORRIGIDA: Permite editar se for o dono E o status for 'rascunho' ou 'reprovado'
        if (auth()->user()->id !== $projeto->user_id || !in_array($resultado->status, ['rascunho', 'reprovado'])) {
            abort(403, 'Acesso não autorizado ou ação não permitida no status atual.');
        }

        return view('resultados.edit', compact('resultado'));
    }

    /**
     * Atualiza um resultado existente no banco de dados.
     */
    public function update(Request $request, Resultado $resultado)
    {
        $projeto = $resultado->projeto;
        
        // LÓGICA CORRIGIDA: Permite atualizar se for o dono E o status for 'rascunho' ou 'reprovado'
        if (auth()->user()->id !== $projeto->user_id || !in_array($resultado->status, ['rascunho', 'reprovado'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $validatedData = $request->validate([
            'atividades_desenvolvidas' => 'required|string',
            // ... (outras regras de validação)
        ]);
        
        $resultado->update($validatedData);

        // Se o aluno está editando um resultado reprovado, ele deve ser reenviado para avaliação.
        if ($resultado->status === 'reprovado') {
            $resultado->status = 'enviado';
            $resultado->aprovado_napex = 'pendente';
            $resultado->parecer_napex = null;
            $resultado->aprovado_coordenador = 'pendente';
            $resultado->parecer_coordenador = null;
            $resultado->save();
        }
    
        return redirect()->route('projetos.index')->with('success', 'Relatório de Resultados atualizado com sucesso!');
    }

    // A função avaliar() que criamos anteriormente continua aqui...
    public function avaliar(Request $request, Resultado $resultado)
    {
        // ... (código do método avaliar)
    }
}