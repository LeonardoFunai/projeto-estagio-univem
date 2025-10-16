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

                {{-- Mostra para Admin e Napex --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'napex')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Pareceres NAPEX</h3>
                    <canvas id="napexChart"></canvas>
                </div>
                @endif
                
                {{-- Mostra para Admin e Coordenadores --}}
                @if(auth()->user()->role === 'admin' || str_starts_with(auth()->user()->role, 'coordenador'))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Pareceres Coordenação de Curso</h3>
                    <canvas id="coordChart"></canvas>
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
    // Configuração global para o plugin que mostra os números nos gráficos
    Chart.register(ChartDataLabels);
    Chart.defaults.set('plugins.datalabels', {
        color: '#FFF',
        font: {
            weight: 'bold'
        },
        formatter: (value) => value > 0 ? value : '', // Só mostra o número se for maior que zero
    });

    document.addEventListener('DOMContentLoaded', function () {
        // --- GRÁFICOS DE STATUS ---
        const statusPropostaCtx = document.getElementById('statusPropostaChart').getContext('2d');
        new Chart(statusPropostaCtx, { type: 'pie', data: { labels: @json($statusPropostaCounts->keys()), datasets: [{ data: @json($statusPropostaCounts->values()) }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });

        const statusResultadoCtx = document.getElementById('statusResultadoChart').getContext('2d');
        new Chart(statusResultadoCtx, { type: 'doughnut', data: { labels: @json($statusResultadoCounts->keys()), datasets: [{ data: @json($statusResultadoCounts->values()) }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });

        // --- GRÁFICOS DE PARECERES (renderiza apenas se o elemento <canvas> existir na página) ---
        if (document.getElementById('napexChart')) {
            const napexCtx = document.getElementById('napexChart').getContext('2d');
            new Chart(napexCtx, { type: 'doughnut', data: { labels: ['Aprovados', 'Reprovados', 'Pendentes'], datasets: [{ data: [{{ $napexCounts['sim'] ?? 0 }}, {{ $napexCounts['nao'] ?? 0 }}, {{ $napexCounts['pendente'] ?? 0 }}] }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }
        
        if (document.getElementById('coordChart')) {
            const coordCtx = document.getElementById('coordChart').getContext('2d');
            new Chart(coordCtx, { type: 'doughnut', data: { labels: ['Aprovados', 'Reprovados', 'Pendentes'], datasets: [{ data: [{{ $coordCounts['sim'] ?? 0 }}, {{ $coordCounts['nao'] ?? 0 }}, {{ $coordCounts['pendente'] ?? 0 }}] }] }, options: { responsive: true, plugins: { legend: { position: 'top' } } } });
        }

        // --- OUTROS GRÁFICOS ---
        const reprovacoesCtx = document.getElementById('reprovacoesChart').getContext('2d');
        new Chart(reprovacoesCtx, { type: 'bar', data: { labels: Object.keys(@json($dadosReprovacoes)), datasets: [{ label: 'Nº de Projetos', data: Object.values(@json($dadosReprovacoes)) }] }, options: { plugins: { datalabels: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
        
        if (document.getElementById('cursosChart')) {
            const cursosCtx = document.getElementById('cursosChart').getContext('2d');
            new Chart(cursosCtx, { type: 'bar', data: { labels: Object.keys(@json($projetosPorCurso)), datasets: [{ label: 'Nº de Projetos', data: Object.values(@json($projetosPorCurso)) }] }, options: { plugins: { datalabels: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
        }
    });
</script>
</x-app-layout>