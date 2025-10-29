<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Rejeicao;
use App\Models\RejeicaoResultado;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlunoDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Query base para projetos do aluno
        $projetosQuery = Projeto::where('user_id', $user->id);

        // 2. Contagem total
        $totalProjetos = (clone $projetosQuery)->count();

        // 3. Contagem por status
        $statusCounts = (clone $projetosQuery)
            ->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status');

        // 4. Contagem de finalizados
        $finalizados = (clone $projetosQuery)->where('etapa', 'Concluído')->count();

        // 5. Contagem de reprovações TOTAIS
        $projetoIds = (clone $projetosQuery)->pluck('id');
        $rejeicoesProposta = Rejeicao::whereIn('projeto_id', $projetoIds)->count();
        
        $resultadoIds = Resultado::whereIn('projeto_id', $projetoIds)->pluck('id');
        $rejeicoesResultado = RejeicaoResultado::whereIn('resultado_id', $resultadoIds)->count();
        
        $totalReprovacoes = $rejeicoesProposta + $rejeicoesResultado;

        // Monta os cards de estatísticas do aluno
        $statCards = [
            [
                'title' => 'Projetos Criados',
                'value' => $totalProjetos,
                'color' => 'blue'
            ],
            [
                'title' => 'Projetos Finalizados',
                'value' => $finalizados,
                'color' => 'green'
            ],
            [
                'title' => 'Em Editando',
                'value' => $statusCounts->get('editando', 0),
                'color' => 'yellow'
            ],
            [
                'title' => 'Aguardando Avaliação',
                'value' => $statusCounts->get('entregue', 0), // Status 'entregue'
                'color' => 'orange',
                'pulse' => true // Adiciona o pulsar
            ],
            [
                'title' => 'Total de Reprovações',
                'value' => $totalReprovacoes,
                'color' => 'red'
            ],
        ];

        // --- NOVA IMPLEMENTAÇÃO: Buscar projetos para a tabela ---
        
        // Buscamos os projetos com as relações de reprovações
        $projetos = Projeto::where('user_id', $user->id)
            ->with(['rejeicoes', 'resultado.rejeicoes']) // Eager load
            ->orderBy('updated_at', 'desc') // Mais recentes primeiro
            ->get();


        return view('aluno.dashboard', compact('statCards', 'projetos'));
    }
}