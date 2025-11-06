<x-app-layout>
    {{-- Scripts dos gráficos carregados apenas nesta página --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    {{-- ### INÍCIO: ESTILO DE ANIMAÇÃO PULSAR ### --}}
    <style>
        @keyframes pulse-deep {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-pulse-deep {
            animation: pulse-deep 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    {{-- ### FIM: ESTILO DE ANIMAÇÃO PULSAR ### --}}

    <x-slot name="pageTitle">
        Dashboard de Projetos
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ### INÍCIO DA SEÇÃO DE STAT CARDS ATUALIZADA ### --}}
            @if(isset($statCards) && count($statCards) > 0)
                <div class="mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                    @foreach($statCards as $card)
                        @php
                            // ATUALIZADO: Cores trocadas para 'orange' e 'red'
                            $colorClasses = match($card['color'] ?? 'gray') {
                                'orange' => 'border-l-4 border-orange-500', // Aguardando
                                'green'  => 'border-l-4 border-green-500',
                                'red'    => 'border-l-4 border-red-500',    // Reprovados
                                'blue'   => 'border-l-4 border-blue-500',
                                'yellow' => 'border-l-4 border-yellow-500',
                                default  => 'border-l-4 border-gray-400',
                            };
                            $textColorClasses = match($card['color'] ?? 'gray') {
                                'orange' => 'text-orange-700', // Aguardando
                                'green'  => 'text-green-700',
                                'red'    => 'text-red-700',    // Reprovados
                                'blue'   => 'text-blue-700',
                                'yellow' => 'text-yellow-700',
                                default  => 'text-gray-700',
                            };
                            // ATUALIZADO: Adiciona classe de animação se a cor for 'orange' (Aguardando Avaliação)
                            $animationClass = $card['color'] === 'orange' ? ' animate-pulse-deep' : '';
                        @endphp
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 {{ $colorClasses }}{{ $animationClass }}">
                            <h4 class="text-xs font-semibold uppercase text-gray-500 tracking-wider">{{ $card['title'] }}</h4>
                            <p class="text-3xl font-bold {{ $textColorClasses }} mt-2">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
            {{-- ### FIM DA NOVA SEÇÃO DE STAT CARDS ### --}}


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Gráficos Visíveis para TODOS --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Status Geral das Propostas</h3>
                    {{-- Wrapper com altura definida --}}
                    <div class="relative h-80">
                        <canvas id="statusPropostaChart"></canvas>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Status Geral dos Relatórios</h3>
                    {{-- Wrapper com altura definida --}}
                    <div class="relative h-80">
                        <canvas id="statusResultadoChart"></canvas>
                    </div>
                </div>
                

                
                {{-- ### Bloco do Gráfico de Coordenação Removido ### --}}

                {{-- GRÁFICO DE PARECERES POR CURSO (Ocupa a linha toda) --}}
                @if(!empty($pareceresPorCurso))
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Análise de Pareceres por Curso</h3>
                    {{-- Wrapper com altura definida --}}
                    <div class="relative h-80">
                        <canvas id="pareceresPorCursoChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Gráfico de Reprovações (Ocupa a linha toda) --}}
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Análise de Reprovações (Projetos Concluídos)</h3>
                    {{-- Wrapper com altura definida --}}
                    <div class="relative h-80">
                        <canvas id="reprovacoesChart"></canvas>
                    </div>
                </div>

                {{-- Mostra apenas para Admin e Napex (Ocupa a linha toda) --}}
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'napex')
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-lg text-gray-800 mb-4">Total de Projetos por Curso</h3>
                    {{-- Wrapper com altura definida --}}
                    <div class="relative h-80">
                        <canvas id="cursosChart"></canvas>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Configuração global para o plugin que mostra os números nos gráficos
        Chart.register(ChartDataLabels);
        
        // --- REMOVIDO DEFAULT GLOBAL ---

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

        // --- GRÁFICO 1: STATUS PROPOSTA (MODIFICADO DE VOLTA PARA PIZZA) ---
        const statusPropostaLabels = @json($statusPropostaCounts->keys());
        const statusPropostaData = @json($statusPropostaCounts->values());
        if (document.getElementById('statusPropostaChart')) {
            const statusPropostaCtx = document.getElementById('statusPropostaChart').getContext('2d');
            new Chart(statusPropostaCtx, { 
                type: 'pie', // MUDADO DE VOLTA
                data: { 
                    labels: statusPropostaLabels, 
                    datasets: [{ 
                        data: statusPropostaData, 
                        backgroundColor: getColorsForLabels(statusPropostaLabels), 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { position: 'top' }, // MUDADO DE VOLTA
                        datalabels: { // ADICIONADO (Config local para números internos)
                            color: '#FFF',
                            font: { weight: 'bold' },
                            formatter: (value) => value > 0 ? value : '',
                        }
                    }
                } 
            });
        }

        // --- GRÁFICO 2: STATUS RESULTADO (MODIFICADO DE VOLTA PARA ROSCA) ---
        const statusResultadoLabels = @json($statusResultadoCounts->keys());
        const statusResultadoData = @json($statusResultadoCounts->values());
        if(document.getElementById('statusResultadoChart')){
            const statusResultadoCtx = document.getElementById('statusResultadoChart').getContext('2d');
            new Chart(statusResultadoCtx, { 
                type: 'doughnut', // MUDADO DE VOLTA
                data: { 
                    labels: statusResultadoLabels, 
                    datasets: [{ 
                        data: statusResultadoData, 
                        backgroundColor: getColorsForLabels(statusResultadoLabels), 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { position: 'top' }, // MUDADO DE VOLTA
                        datalabels: { // ADICIONADO (Config local para números internos)
                            color: '#FFF',
                            font: { weight: 'bold' },
                            formatter: (value) => value > 0 ? value : '',
                        }
                    }
                } 
            });
        }

        // --- GRÁFICO 3: NAPEX (Com config local de datalabels) ---
        if (document.getElementById('napexChart')) {
            const napexCtx = document.getElementById('napexChart').getContext('2d');
            new Chart(napexCtx, { 
                type: 'doughnut', 
                data: { 
                    labels: ['Aprovados', 'Reprovados', 'Pendentes'], 
                    datasets: [{ 
                        data: [{{ $napexCounts['sim'] ?? 0 }}, {{ $napexCounts['nao'] ?? 0 }}, {{ $napexCounts['pendente'] ?? 0 }}], 
                        backgroundColor: [parecerColors.Aprovados, parecerColors.Reprovados, parecerColors.Pendentes], 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { position: 'top' },
                        datalabels: { // ADICIONADO (Config local para números internos)
                            color: '#FFF',
                            font: { weight: 'bold' },
                            formatter: (value) => value > 0 ? value : '',
                        }
                    } 
                } 
            });
        }

        // --- GRÁFICO 4: COORDENAÇÃO (REMOVIDO) ---
        
        // --- GRÁFICO 5: PARECERES POR CURSO (Com config local de datalabels) ---
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
                    labels: labels, // Mantém os labels originais (nomes dos cursos)
                    datasets: [
                        // Mantém os 6 datasets como você forneceu
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
                    maintainAspectRatio: false, 
                    plugins: {
                        legend: { position: 'top' },
                        datalabels: { // ADICIONADO (Config local para números internos)
                            color: '#FFF',
                            font: { weight: 'bold' },
                            formatter: (value) => value > 0 ? value : '',
                        }
                    },
                    scales: {
                        x: { 
                            ticks: {
                                callback: function(value, index, ticks) {
                                    // Pega o label original (ex: "Ciência da Computação")
                                    const label = this.getLabelForValue(value);
                                    
                                    // Retorna o label em duas linhas
                                    return [label, '(NAPEX / Coordenador)'];
                                }
                            }
                        },
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 } 
                        }
                    }
                }
            });
        }

        // --- GRÁFICO 6: REPROVAÇÕES (COM COR ATUALIZADA) ---
        const reprovacoesCtx = document.getElementById('reprovacoesChart');
        if (reprovacoesCtx) {
            new Chart(reprovacoesCtx.getContext('2d'), { 
                type: 'bar', 
                data: { 
                    labels: Object.keys(@json($dadosReprovacoes)), 
                    datasets: [{ 
                        label: 'Nº de Projetos', 
                        data: Object.values(@json($dadosReprovacoes)), 
                        backgroundColor: '#FCA5A5', // <-- COR ATUALIZADA
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        datalabels: { display: false } 
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
        
        // --- GRÁFICO 7: PROJETOS POR CURSO (Sem datalabels) ---
        const cursosCtx = document.getElementById('cursosChart');
        if (cursosCtx) {
            new Chart(cursosCtx.getContext('2d'), { 
                type: 'bar', 
                data: { 
                    labels: Object.keys(@json($projetosPorCurso)), 
                    datasets: [{ 
                        label: 'Nº de Projetos', 
                        data: Object.values(@json($projetosPorCurso)), 
                        backgroundColor: '#6366F1', 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        datalabels: { display: false } 
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
    });
</script>
</x-app-layout>