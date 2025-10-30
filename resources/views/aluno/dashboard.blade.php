<x-app-layout>
    {{-- Estilo de Animação Pulsar --}}
    <style>
        @keyframes pulse-deep {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-pulse-deep {
            animation: pulse-deep 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        /* Estilos para os badges de status (Não mais usado na tabela) */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-editando { background-color: #FFF3CD; color: #856404; }
        .status-entregue { background-color: #D1E7FD; color: #0C5460; }
        .status-aprovado { background-color: #D4EDDA; color: #155724; }
        .status-reprovado { background-color: #F8D7DA; color: #721C24; }
        .status-concluido { background-color: #D1FAE5; color: #065F46; }
        .status-default { background-color: #E2E3E5; color: #383D41; }
    </style>

    <x-slot name="pageTitle">
        Meu Início
    </x-slot>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold">Olá, {{ auth()->user()->name }}!</h2>
                    <p class="mt-2 text-gray-600">Aqui está um resumo dos seus projetos de extensão.</p>
                </div>
            </div>

            @if(isset($statCards) && count($statCards) > 0)
                <div class="mb-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($statCards as $card)
                        @php
                            $colorClasses = match($card['color'] ?? 'gray') {
                                'orange' => 'border-l-4 border-orange-500',
                                'green'  => 'border-l-4 border-green-500',
                                'red'    => 'border-l-4 border-red-500',
                                'blue'   => 'border-l-4 border-blue-500',
                                'yellow' => 'border-l-4 border-yellow-500',
                                default  => 'border-l-4 border-gray-400',
                            };
                            $textColorClasses = match($card['color'] ?? 'gray') {
                                'orange' => 'text-orange-700',
                                'green'  => 'text-green-700',
                                'red'    => 'text-red-700',
                                'blue'   => 'text-blue-700',
                                'yellow' => 'text-yellow-700',
                                default  => 'text-gray-700',
                            };
                            $animationClass = ($card['pulse'] ?? false) ? ' animate-pulse-deep' : '';
                        @endphp
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 {{ $colorClasses }}{{ $animationClass }}">
                            <h4 class="text-xs font-semibold uppercase text-gray-500 tracking-wider">{{ $card['title'] }}</h4>
                            <p class="text-3xl font-bold {{ $textColorClasses }} mt-2">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Meus Projetos</h3>
                    
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Projeto
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Andamento
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Etapa
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reprovações
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($projetos as $projeto)
                                    <tr>
                                        <td class="px-6 py-4 max-w-xs">
                                            <div class="text-sm font-medium text-gray-900 whitespace-normal break-words">{{ $projeto->titulo }}</div>
                                            <div class="text-sm text-gray-500">{{ $projeto->user->curso->nome ?? 'Curso não definido' }}</div>
                                        </td>
                                        
                                        {{-- =================================== --}}
                                        {{-- ========= CÉLULA DE FLUXO ========= --}}
                                        {{-- =================================== --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            
                                            @if($projeto->etapa == 'Proposta' || $projeto->etapa == 'Relatório')
                                                @php
                                                    $isAtual = $projeto->status;
                                                    $isReprovado = $isAtual === 'reprovado';
                                                    $isAprovado = $isAtual === 'aprovado';
                                                    
                                                    $pareceresObj = null;
                                                    if ($projeto->etapa == 'Relatório' && $projeto->resultado) {
                                                        $pareceresObj = $projeto->resultado;
                                                    } else {
                                                        $pareceresObj = $projeto;
                                                    }

                                                    $napexAprovado = $pareceresObj->aprovado_napex === 'sim';
                                                    $coordAprovado = $pareceresObj->aprovado_coordenador === 'sim';
                                                    $napexReprovado = $pareceresObj->aprovado_napex === 'nao';
                                                    $coordReprovado = $pareceresObj->aprovado_coordenador === 'nao';
                                                    
                                                    $stepIndex = 0;
                                                    if ($isAtual == 'editando') $stepIndex = 1;
                                                    if ($isAtual == 'entregue') $stepIndex = 2;
                                                    if ($isAtual == 'entregue' && ($napexAprovado || $coordAprovado || $napexReprovado || $coordReprovado)) $stepIndex = 3; 
                                                    if ($isAprovado || $isReprovado) $stepIndex = 4;

                                                    if ($projeto->etapa == 'Relatório' && $isAtual == 'aprovado') {
                                                        $stepIndex = 0;
                                                        $isAprovado = false;
                                                    }

                                                    // MUDANÇA AQUI: O último item é neutro ("Avaliação")
                                                    $steps = ['Criado', 'Editando', 'Entregue', 'Em Análise', 'Avaliação'];
                                                @endphp
                                                
                                                {{-- Container do Fluxo --}}
                                                <div class="flex items-center space-x-1" style="min-width: 250px;">
                                                    @foreach($steps as $index => $step)
                                                        @php
                                                            $isCompleted = ($index < $stepIndex) || ($isAprovado && $index <= 4);
                                                            $isCurrent = ($index == $stepIndex) && !$isReprovado && !$isAprovado;
                                                            $isFailed = $isReprovado && ($index >= $stepIndex);
                                                        @endphp
                                                        <div class="flex items-center" title="{{ $step }} ({{ $projeto->etapa }})">
                                                            {{-- Círculo --}}
                                                            <span @class([
                                                                'flex items-center justify-center w-5 h-5 rounded-full font-bold text-xs',
                                                                'bg-green-500 text-white' => $isCompleted,
                                                                'bg-blue-600 text-white animate-pulse' => $isCurrent,
                                                                'bg-red-500 text-white' => $isFailed,
                                                                'bg-gray-300 text-gray-600' => !$isCompleted && !$isCurrent && !$isFailed,
                                                            ])>
                                                                {{ $index + 1 }}
                                                            </span>

                                                            {{-- Texto (com lógica condicional) --}}
                                                            <span @class([
                                                                'ml-1 font-semibold hidden md:inline text-xs',
                                                                'text-green-600' => $isCompleted,
                                                                'text-blue-700' => $isCurrent,
                                                                'text-red-600' => $isFailed,
                                                                'text-gray-500' => !$isCompleted && !$isCurrent && !$isFailed,
                                                            ])>
                                                                {{-- ================================================ --}}
                                                                {{-- Lógica condicional para a última etapa --}}
                                                                {{-- ================================================ --}}
                                                                @if ($index == 4) {{-- Se for a 5ª etapa --}}
                                                                    @if ($isFailed)
                                                                        Reprovado
                                                                    @elseif ($isCompleted)
                                                                        Aprovado
                                                                    @else
                                                                        {{ $step }} {{-- "Avaliação" --}}
                                                                    @endif
                                                                @else
                                                                    {{ $step }}
                                                                @endif
                                                                {{-- ================================================ --}}
                                                            </span>
                                                            
                                                            {{-- Seta (responsiva) --}}
                                                            <span class="ml-1 text-gray-300 text-xs hidden sm:inline">
                                                                @if(!$loop->last) -> @endif
                                                            </span>
                                                            <span class="ml-1 text-gray-300 text-xs sm:hidden">
                                                                @if(!$loop->last) > @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            
                                            @elseif($projeto->etapa == 'Concluído')
                                                <span class="status-badge status-concluido">
                                                    Projeto Concluído
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $projeto->etapa }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                            @php
                                                $reprovacoesProposta = $projeto->rejeicoes->count();
                                                $reprovacoesResultado = $projeto->resultado?->rejeicoes->count() ?? 0;
                                                $totalReprovacoesProjeto = $reprovacoesProposta + $reprovacoesResultado;
                                            @endphp
                                            
                                            <span class="{{ $totalReprovacoesProjeto > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $totalReprovacoesProjeto }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('projetos.show', $projeto) }}" class="text-indigo-600 hover:text-indigo-900">Visualizar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            Você ainda não criou nenhum projeto. <a href="{{ route('projetos.create') }}" class="text-indigo-600 hover:text-indigo-900">Crie um agora!</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800">Convites Pendentes</h3>
                    <p class="mt-2 text-gray-600">
                        Verifique se você possui convites para participar de outros projetos.
                    </pos>
                    <a href="{{ route('convites.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                        Ver Meus Convites
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>