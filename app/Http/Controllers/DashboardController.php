<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $projetoQuery = Projeto::query(); // Query base para projetos

        // Filtro principal: se for coordenador, restringe a query aos seus cursos.
        // Admin e Napex não entram no if, então a query continua ampla para eles.
        if (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            $projetoQuery->whereHas('user', function ($q) use ($cursosCoordenadosIds) {
                $q->whereIn('curso_id', $cursosCoordenadosIds);
            });
        }

        // --- DADOS PARA OS GRÁFICOS ---

        // 1. Status Geral das PROPOSTAS
        // Usa a $projetoQuery, então já vem filtrada por perfil.
        $statusPropostaCounts = (clone $projetoQuery)->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status');

        // 2. Status Geral dos RELATÓRIOS de Mensuração
        $resultadoQuery = Resultado::query();
        if (str_starts_with($user->role, 'coordenador')) {
            // Re-aplica o filtro para os resultados, através do projeto associado
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            $resultadoQuery->whereHas('projeto.user', function ($q) use ($cursosCoordenadosIds) {
                $q->whereIn('curso_id', $cursosCoordenadosIds);
            });
        }
        $statusResultadoCounts = $resultadoQuery->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status');

        // 3. Dados de Pareceres NAPEX
        $napexCounts = (clone $projetoQuery)->groupBy('aprovado_napex')
            ->select('aprovado_napex', DB::raw('count(*) as total'))
            ->pluck('total', 'aprovado_napex');

        // 4. Dados de Pareceres Coordenação
        $coordCounts = (clone $projetoQuery)->groupBy('aprovado_coordenador')
            ->select('aprovado_coordenador', DB::raw('count(*) as total'))
            ->pluck('total', 'aprovado_coordenador');

        // 5. Análise de Reprovações
        $projetosFinalizadosIds = (clone $projetoQuery)->whereIn('status', ['finalizado', 'aprovado'])->pluck('id');
        $reprovacoesPorProjeto = DB::table('rejeicoes')
            ->whereIn('projeto_id', $projetosFinalizadosIds)
            ->groupBy('projeto_id')
            ->select('projeto_id', DB::raw('count(*) as total_reprovacoes'))
            ->pluck('total_reprovacoes', 'projeto_id');
        
        $contagemProjetosComReprovacoes = $reprovacoesPorProjeto->countBy();
        $totalProjetosFinalizados = $projetosFinalizadosIds->count();
        $totalProjetosComAlgumaReprovacao = $reprovacoesPorProjeto->count();
        
        $dadosReprovacoes = collect([
            'Nenhuma' => $totalProjetosFinalizados - $totalProjetosComAlgumaReprovacao,
            '1 vez' => $contagemProjetosComReprovacoes->get(1, 0),
            '2 vezes' => $contagemProjetosComReprovacoes->get(2, 0),
            '3+ vezes' => $reprovacoesPorProjeto->filter(fn ($count) => $count >= 3)->count(),
        ]);

        // 6. Projetos por Curso (só é relevante para Admin e Napex, mas calculamos sempre para simplificar)
        $projetosPorCurso = Projeto::with('user.curso') // Usa uma nova query sem filtro de coordenador
            ->get()
            ->groupBy('user.curso.nome')
            ->map(fn ($group) => $group->count());

        // Retorna a view com todas as variáveis necessárias
        return view('dashboard', compact(
            'statusPropostaCounts',
            'statusResultadoCounts',
            'napexCounts',
            'coordCounts',
            'projetosPorCurso',
            'dadosReprovacoes'
        ));
    }
}