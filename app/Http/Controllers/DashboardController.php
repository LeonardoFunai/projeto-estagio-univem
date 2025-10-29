<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\Resultado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Curso;

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
        $statusPropostaCounts = (clone $projetoQuery)->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status');

        // 2. Status Geral dos RELATÓRIOS de Mensuração
        $resultadoQuery = Resultado::query();
        if (str_starts_with($user->role, 'coordenador')) {
            $cursosCoordenadosIds = $user->cursosCoordenados()->pluck('cursos.id');
            $resultadoQuery->whereHas('projeto.user', function ($q) use ($cursosCoordenadosIds) {
                $q->whereIn('curso_id', $cursosCoordenadosIds);
            });
        }
        $statusResultadoCounts = $resultadoQuery->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status');

        // 3. Dados de Pareceres NAPEX (considera proposta e resultado)
        $napexCountsQuery = (clone $projetoQuery);
        // Ajuste para garantir que a query base (com filtro de coordenador, se aplicável) seja usada
        $napexCountsProposta = (clone $napexCountsQuery)->where('etapa', 'Proposta')->select('aprovado_napex', DB::raw('count(*) as total'))->groupBy('aprovado_napex')->pluck('total', 'aprovado_napex');
        
        $projetoIdsEtapaResultado = (clone $napexCountsQuery)->where('etapa', 'Resultado')->pluck('id');
        $napexCountsResultado = Resultado::whereIn('projeto_id', $projetoIdsEtapaResultado)->select('aprovado_napex', DB::raw('count(*) as total'))->groupBy('aprovado_napex')->pluck('total', 'aprovado_napex');
        
        // Combina os resultados
        $keys = $napexCountsProposta->keys()->merge($napexCountsResultado->keys())->unique();
        $napexCounts = $keys->combine($keys->map(fn($key) => $napexCountsProposta->get($key, 0) + $napexCountsResultado->get($key, 0)));


        // 4. Dados de Pareceres Coordenação (considera proposta e resultado)
        $coordCountsQuery = (clone $projetoQuery);
        $coordCountsProposta = (clone $coordCountsQuery)->where('etapa', 'Proposta')->select('aprovado_coordenador', DB::raw('count(*) as total'))->groupBy('aprovado_coordenador')->pluck('total', 'aprovado_coordenador');
        
        $projetoIdsEtapaResultadoCoord = (clone $coordCountsQuery)->where('etapa', 'Resultado')->pluck('id');
        $coordCountsResultado = Resultado::whereIn('projeto_id', $projetoIdsEtapaResultadoCoord)->select('aprovado_coordenador', DB::raw('count(*) as total'))->groupBy('aprovado_coordenador')->pluck('total', 'aprovado_coordenador');
        
        $keysCoord = $coordCountsProposta->keys()->merge($coordCountsResultado->keys())->unique();
        $coordCounts = $keysCoord->combine($keysCoord->map(fn($key) => $coordCountsProposta->get($key, 0) + $coordCountsResultado->get($key, 0)));


        // 5. Análise de Reprovações
        $projetosFinalizadosIds = (clone $projetoQuery)->whereIn('etapa', ['Concluído'])->pluck('id');
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

        // 6. Projetos por Curso (só é relevante para Admin e Napex)
        // Se for admin/napex, busca todos. Se for coordenador, usa a query base filtrada.
        if ($user->role === 'admin' || $user->role === 'napex') {
            $projetosPorCurso = Projeto::with('user.curso')
                ->get()
                ->groupBy('user.curso.nome')
                ->map(fn ($group) => $group->count());
        } else {
             $projetosPorCurso = (clone $projetoQuery)->with('user.curso')
                ->get()
                ->groupBy('user.curso.nome')
                ->map(fn ($group) => $group->count());
        }
            
        // --- GRÁFICO: 7. Análise de Pareceres por Curso ---
        $pareceresPorCurso = [];
        // Se for coordenador, pega apenas os cursos que ele coordena. Senão, todos.
        $cursos = str_starts_with($user->role, 'coordenador') 
            ? $user->cursosCoordenados
            : Curso::all();

        foreach ($cursos as $curso) {
            $pareceresPorCurso[$curso->nome] = [
                'napex' => ['sim' => 0, 'nao' => 0, 'pendente' => 0],
                'coordenador' => ['sim' => 0, 'nao' => 0, 'pendente' => 0],
            ];
        }

        // Usa a query já filtrada por perfil
        $projetosParaAvaliar = (clone $projetoQuery)->with(['user.curso', 'resultado'])
            ->where('status', '!=', 'editando')
            ->where('etapa', '!=', 'Concluído') // Exclui projetos já concluídos
            ->get();

        foreach ($projetosParaAvaliar as $projeto) {
            if (!$projeto->user->curso) continue;
            
            $cursoNome = $projeto->user->curso->nome;

            // Se o curso não estiver na lista (relevante para coordenador), pula
            if (!isset($pareceresPorCurso[$cursoNome])) continue;

            $fonte = ($projeto->etapa === 'Resultado' && $projeto->resultado) ? $projeto->resultado : $projeto;

            $mapStatus = fn($valor) => $valor === 'sim' ? 'sim' : ($valor === 'nao' ? 'nao' : 'pendente');

            $pareceresPorCurso[$cursoNome]['napex'][$mapStatus($fonte->aprovado_napex)]++;
            $pareceresPorCurso[$cursoNome]['coordenador'][$mapStatus($fonte->aprovado_coordenador)]++;
        }

        // --- NOVO: DADOS PARA OS STAT CARDS (REORDENADOS E ATUALIZADOS) ---
        $statCards = [];
        $userRole = $user->role;

        // 1. "Aguardando Avaliação" (Pendentes)
        if ($userRole === 'napex') {
            $statCards[] = [
                'title' => 'Aguardando Avaliação (NAPEX)',
                'value' => $napexCounts->get('pendente', 0),
                'color' => 'orange' // <-- MUDADO PARA LARANJA
            ];
        } elseif (str_starts_with($userRole, 'coordenador')) {
            $statCards[] = [
                'title' => 'Aguardando Avaliação (Coord.)',
                'value' => $coordCounts->get('pendente', 0),
                'color' => 'orange' // <-- MUDADO PARA LARANJA
            ];
        }

        // 2. Aprovados
        $totalAprovados = $statusPropostaCounts->get('aprovado', 0) + $statusResultadoCounts->get('aprovado', 0);
        $statCards[] = [
            'title' => 'Aprovados',
            'value' => $totalAprovados,
            'color' => 'green'
        ];

        // 3. Reprovados
        $totalReprovados = $statusPropostaCounts->get('reprovado', 0) + $statusResultadoCounts->get('reprovado', 0);
        $statCards[] = [
            'title' => 'Reprovados',
            'value' => $totalReprovados,
            'color' => 'red' // <-- MUDADO PARA VERMELHO
        ];
        
        // 4. Em Editando
        $totalEditando = $statusPropostaCounts->get('editando', 0) + $statusResultadoCounts->get('editando', 0);
        $statCards[] = [
            'title' => 'Em Editando',
            'value' => $totalEditando,
            'color' => 'yellow'
        ];

        // 5. Finalizados
        $statCards[] = [
            'title' => 'Finalizados',
            'value' => $totalProjetosFinalizados,
            'color' => 'blue'
        ];

        // 6. Fase Proposta
        $totalProposta = $statusPropostaCounts->sum();
        $statCards[] = [
            'title' => 'Fase Proposta',
            'value' => $totalProposta,
            'color' => 'gray'
        ];

        // 7. Fase Resultado
        $totalResultado = $statusResultadoCounts->sum();
        $statCards[] = [
            'title' => 'Fase Resultado',
            'value' => $totalResultado,
            'color' => 'gray'
        ];


        // Retorna a view com todas as variáveis necessárias
        return view('dashboard', compact(
            'statusPropostaCounts',
            'statusResultadoCounts',
            'napexCounts',
            'coordCounts',
            'projetosPorCurso',
            'dadosReprovacoes',
            'pareceresPorCurso',
            'statCards' // <-- Variável nova adicionada aqui
        ));
    }
}