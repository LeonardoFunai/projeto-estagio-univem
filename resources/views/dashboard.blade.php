<x-app-layout>
    {{-- Scripts dos gráficos carregados apenas nesta página --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <x-slot name="pageTitle">
        Dashboard de Projetos
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Gráficos Visíveis para TODOS --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Status Geral das Propostas</h3>
                    <canvas id="statusPropostaChart"></canvas>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Status Geral dos Relatórios</h3>
                    <canvas id="statusResultadoChart"></canvas>
                </div>
                
                {{-- GRÁFICOS CONDICIONAIS POR PERFIL --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'napex')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Pareceres NAPEX</h3>
                    <canvas id="napexChart"></canvas>
                </div>
                @endif
                
                @if(auth()->user()->role === 'admin' || str_starts_with(auth()->user()->role, 'coordenador'))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Pareceres Coordenação de Curso</h3>
                    <canvas id="coordChart"></canvas>
                </div>
                @endif

                {{-- GRÁFICO DE PARECERES POR CURSO (Ocupa a linha toda) --}}
                @if(!empty($pareceresPorCurso))
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Análise de Pareceres por Curso</h3>
                    <canvas id="pareceresPorCursoChart"></canvas>
                </div>
                @endif

                {{-- Gráfico de Reprovações (Ocupa a linha toda) --}}
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Análise de Reprovações (Projetos Concluídos)</h3>
                    <canvas id="reprovacoesChart"></canvas>
                </div>

                {{-- Mostra apenas para Admin e Napex (Ocupa a linha toda) --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'napex')
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Total de Projetos por Curso</h3>
                    <canvas id="cursosChart"></canvas>
                </div>
                @endif
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Configuração global para o plugin que mostra os números nos gráficos
        Chart.register(ChartDataLabels);
        Chart.defaults.set('plugins.datalabels', {
            color: '#FFF',
            font: { weight: 'bold' },
            formatter: (value) => value > 0 ? value : '',
        });

        // --- Paleta de Cores Padrão ---
        const colors = {
            editando: '#ff9f05ff',
            entregue: '#60A5FA',
            aprovado: '#00d64fff',
            reprovado: '#F87171',
            pendente: '#9CA3AF',
            finalizado: '#4ADE80',
        };
        const parecerColors = {
            'Aprovados': colors.aprovado,
            'Reprovados': colors.reprovado,
            'Pendentes': colors.pendente,
        };
        const getColorsForLabels = (labels) => {
            return labels.map(label => colors[label.toLowerCase()] || '#E5E7EB');
        };

        // --- GRÁFICOS DE STATUS E PARECERES (Sem alterações) ---
        const statusPropostaLabels = @json($statusPropostaCounts->keys());
        const statusPropostaData = @json($statusPropostaCounts->values());
        if (document.getElementById('statusPropostaChart')) {
            const statusPropostaCtx = document.getElementById('statusPropostaChart').getContext('2d');
            new Chart(statusPropostaCtx, { type: 'pie', data: { labels: statusPropostaLabels, datasets: [{ data: statusPropostaData, backgroundColor: getColorsForLabels(statusPropostaLabels), }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }
        const statusResultadoLabels = @json($statusResultadoCounts->keys());
        const statusResultadoData = @json($statusResultadoCounts->values());
        if(document.getElementById('statusResultadoChart')){
            const statusResultadoCtx = document.getElementById('statusResultadoChart').getContext('2d');
            new Chart(statusResultadoCtx, { type: 'doughnut', data: { labels: statusResultadoLabels, datasets: [{ data: statusResultadoData, backgroundColor: getColorsForLabels(statusResultadoLabels), }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }
        if (document.getElementById('napexChart')) {
            const napexCtx = document.getElementById('napexChart').getContext('2d');
            new Chart(napexCtx, { type: 'doughnut', data: { labels: ['Aprovados', 'Reprovados', 'Pendentes'], datasets: [{ data: [{{ $napexCounts['sim'] ?? 0 }}, {{ $napexCounts['nao'] ?? 0 }}, {{ $napexCounts['pendente'] ?? 0 }}], backgroundColor: [parecerColors.Aprovados, parecerColors.Reprovados, parecerColors.Pendentes], }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }
        if (document.getElementById('coordChart')) {
            const coordCtx = document.getElementById('coordChart').getContext('2d');
            new Chart(coordCtx, { type: 'doughnut', data: { labels: ['Aprovados', 'Reprovados', 'Pendentes'], datasets: [{ data: [{{ $coordCounts['sim'] ?? 0 }}, {{ $coordCounts['nao'] ?? 0 }}, {{ $coordCounts['pendente'] ?? 0 }}], backgroundColor: [parecerColors.Aprovados, parecerColors.Reprovados, parecerColors.Pendentes], }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }
        
        // --- GRÁFICO FINAL: Barras Lado a Lado ---
        if (document.getElementById('pareceresPorCursoChart')) {
            const pareceresCtx = document.getElementById('pareceresPorCursoChart').getContext('2d');
            const dadosGrafico = @json($pareceresPorCurso);
            
            const labels = Object.keys(dadosGrafico);

            const napexData = {
                sim: labels.map(curso => dadosGrafico[curso].napex.sim),
                nao: labels.map(curso => dadosGrafico[curso].napex.nao),
                pendente: labels.map(curso => dadosGrafico[curso].napex.pendente),
            };

            const coordData = {
                sim: labels.map(curso => dadosGrafico[curso].coordenador.sim),
                nao: labels.map(curso => dadosGrafico[curso].coordenador.nao),
                pendente: labels.map(curso => dadosGrafico[curso].coordenador.pendente),
            };

            new Chart(pareceresCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'NAPEX - Aprovado', data: napexData.sim, backgroundColor: colors.aprovado },
                        { label: 'NAPEX - Reprovado', data: napexData.nao, backgroundColor: colors.reprovado },
                        { label: 'NAPEX - Pendente', data: napexData.pendente, backgroundColor: colors.pendente },
                        { label: 'Coordenador - Aprovado', data: coordData.sim, backgroundColor: colors.aprovado },
                        { label: 'Coordenador - Reprovado', data: coordData.nao, backgroundColor: colors.reprovado },
                        { label: 'Coordenador - Pendente', data: coordData.pendente, backgroundColor: colors.pendente },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 } 
                        }
                    }
                }
            });
        }

        // --- OUTROS GRÁFICOS (Sem alterações) ---
        const reprovacoesCtx = document.getElementById('reprovacoesChart');
        if (reprovacoesCtx) {
            new Chart(reprovacoesCtx.getContext('2d'), { type: 'bar', data: { labels: Object.keys(@json($dadosReprovacoes)), datasets: [{ label: 'Nº de Projetos', data: Object.values(@json($dadosReprovacoes)), backgroundColor: '#60A5FA', }] }, options: { plugins: { datalabels: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
        }
        const cursosCtx = document.getElementById('cursosChart');
        if (cursosCtx) {
            new Chart(cursosCtx.getContext('2d'), { type: 'bar', data: { labels: Object.keys(@json($projetosPorCurso)), datasets: [{ label: 'Nº de Projetos', data: Object.values(@json($projetosPorCurso)), backgroundColor: '#6366F1', }] }, options: { plugins: { datalabels: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
        }
    });
</script>
</x-app-layout>