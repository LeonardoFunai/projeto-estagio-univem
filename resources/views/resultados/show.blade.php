@php
    use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="header">
        
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Visualização do Relatório de Resultados
            </h2>
        @php
            $isAluno = auth()->user()->id === $resultado->projeto->user_id;
            
            $podeEditarOuEnviar = in_array($resultado->status, ['editando', 'reprovado']);
            
            
            $podeVoltar = $resultado->status === 'entregue' && 
                        ($resultado->aprovado_napex === 'pendente' || is_null($resultado->aprovado_napex)) && 
                        ($resultado->aprovado_coordenador === 'pendente' || is_null($resultado->aprovado_coordenador));
        @endphp

        </div>
    </x-slot>

    <!-- Trilha de Status -->

    @php
        // --- Lógica para Definir o Estado Atual do Relatório ---
        $resultadoStatus = $resultado->status;

        // Condições de Aprovação e Reprovação
        $napexAprovado = $resultado->aprovado_napex === 'sim';
        $coordAprovado = $resultado->aprovado_coordenador === 'sim';
        $napexReprovado = $resultado->aprovado_napex === 'nao';
        $coordReprovado = $resultado->aprovado_coordenador === 'nao';

        // --- Status Gerais do Fluxo ---
        $propostaAprovada = true; // Etapa 1: Sempre concluída
        $relatorioAdicionado = true; // Etapa 2: Sempre concluída, pois o resultado existe para ser visto
        
        $emEdicao = $resultadoStatus === 'editando'; // Etapa 3: É o estado ATUAL?
        $foiEntregue = in_array($resultadoStatus, ['entregue', 'aprovado', 'reprovado']); 
        
        $emAnalise = $resultadoStatus === 'entregue'; // Etapa 4: É o estado ATUAL?
        
        $reprovadoGeral = $resultadoStatus === 'reprovado';
        $aprovadoFinal = $resultadoStatus === 'aprovado';

        // --- Função Helper de Estilo ---
        function etapaClasseFinal($condicaoPositiva, $isAtual = false, $condicaoNegativa = false) {
            if ($condicaoNegativa) return 'bg-red-500 text-white border-red-600 shadow-md';
            return $condicaoPositiva
                ? 'bg-green-500 text-white border-green-600 shadow-md'
                : ($isAtual ? 'bg-blue-600 text-white border-blue-800 shadow-md animate-pulse' : 'bg-gray-300 text-gray-600 border-gray-400 shadow-sm');
        }
    @endphp

    <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento do Relatório</h3>

    <div class="flex items-center justify-center">
        

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($propostaAprovada) }}">
                <span>✓</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Proposta<br>Aprovada</span>
        </div>

        <div class="w-12 border-t-4 {{ etapaClasseFinal($propostaAprovada) }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($relatorioAdicionado) }}">
                <span>1</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Adicionado</span>
        </div>

        <div class="w-12 border-t-4 {{ $foiEntregue ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($foiEntregue, $emEdicao) }}">
                <span>2</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Edição</span>
        </div>

        

        <div class="w-12 border-t-4 {{ $foiEntregue ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($aprovadoFinal || $reprovadoGeral, $emAnalise) }}">
                <span>3</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Entregue</span>
        </div>

        <div class="w-12 border-t-4 {{ ($napexAprovado || $coordAprovado || $reprovadoGeral) ? ($reprovadoGeral ? 'border-red-500' : 'border-green-500') : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col space-y-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($napexAprovado, false, $napexReprovado) }}">
                    <span class="text-xs font-bold">N</span>
                </div>
                <span class="ml-2 text-sm">Parecer NAPEX</span>
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($coordAprovado, false, $coordReprovado) }}">
                    <span class="text-xs font-bold">C</span>
                </div>
                <span class="ml-2 text-sm">Parecer Coord.</span>
            </div>
        </div>

        <div class="w-12 border-t-4 {{ $aprovadoFinal ? 'border-green-500' : ($reprovadoGeral ? 'border-red-500' : 'border-gray-300') }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($aprovadoFinal, false, $reprovadoGeral) }}">
                <span class="text-2xl">
                    @if($aprovadoFinal) ✓ @endif
                    @if($reprovadoGeral) X @endif
                </span>
            </div>
            <span class="mt-2 text-sm font-semibold">
                @if($reprovadoGeral) Reprovado @else Aprovado @endif
            </span>
        </div>
    </div>

    <div class="py-10">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-3">
            <div class="flex items-center space-x-4">
            
            <!-- Gerar pdf -->
            <a href="{{ route('resultados.gerarPdf', $resultado) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    Gerar PDF do Relatório
                </a>
                <!-- Botão de Voltar para edição -->
                @if ($isAluno && $podeVoltar)
                    <form action="{{ route('resultados.voltarParaRascunho', $resultado) }}" method="POST" onsubmit="return confirm('Tem certeza? A ação removerá o relatório da fila de avaliação e você precisará enviá-lo novamente.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">
                            Voltar para Edição
                        </button>
                    </form>
                @endif

                
                @if ($isAluno && $podeEditarOuEnviar)
                    <!-- Botão de Editar -->
                    <a href="{{ route('resultados.edit', $resultado) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                        Editar Relatório
                    </a>
                    <!-- Botão de Enviar -->
                    <form action="{{ route('resultados.enviar', $resultado) }}" method="POST" onsubmit="return confirm('Você tem certeza que deseja enviar o relatório para avaliação? Você poderá retorná-lo para o modo de edição caso ainda não tenha sido avaliado.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Enviar para Avaliação
                        </button>
                    </form>
                @endif

                <a href="{{ route('projetos.show', $resultado->projeto_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Ver Proposta
                </a>
            </div>

            <!-- Título -->
            <x-slot name="pageTitle">
                Detalhes do Relatório de Resultados
            </x-slot>



            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                
                <h3 class="text-lg font-bold text-gray-800 mb-4">IDENTIFICAÇÃO DO PROJETO</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Título:</strong>
                        <p class="text-gray-900">{{ $resultado->projeto->titulo }}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Período:</strong>
                        <p class="text-gray-900">{{ $resultado->projeto->periodo }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">RELATÓRIO DE RESULTADOS ENTREGUE</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Atividades Desenvolvidas no Período:</strong>
                        <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $resultado->atividades_desenvolvidas }}</p>
                    </div>

                    <hr>

                        <div>
                            <strong class="text-gray-600">Parcerias - Organizações Envolvidas:</strong>
                            @if($resultado->parceiro_organizacao)
                                <ul class="mt-1 list-disc list-inside text-gray-900">
                                    <li><strong>Organização:</strong> {{ $resultado->parceiro_organizacao }}</li>
                                    <li><strong>Responsável:</strong> {{ $resultado->parceiro_responsavel ?? 'N/A' }}</li>
                                    <li><strong>Endereço:</strong> {{ $resultado->parceiro_endereco ?? 'N/A' }}</li>
                                    <li><strong>CNPJ:</strong> {{ $resultado->parceiro_cnpj ?? 'N/A' }}</li>
                                    <li><strong>Participação:</strong> {{ $resultado->parceiro_tipo_participacao ?? 'N/A' }}</li>
                                </ul>
                            @else
                                <p class="mt-1 text-gray-900">Nenhuma parceria envolvida.</p>
                            @endif
                        </div>
                    
                    <hr>
                        <div>
                            <strong class="text-gray-600">Pessoas da Comunidade Externa Envolvidas:</strong>
                            <p class="mt-1 text-gray-900">{{ $resultado->comunidade_externa ?? 'Não informado' }}</p>
                        </div>
                    <hr>

                    <div class="mt-8">
                        <h3 class="text-xl font-semibold border-b pb-2">Anexos do Relatório</h3>

                        {{-- Mostra a DESCRIÇÃO DOS ANEXOS que o aluno escreveu --}}
                        @if ($resultado->anexos_descricao)
                            <div class="mt-4">
                                <p class="font-semibold text-gray-700">Descrição Fornecida:</p>
                                <p class="mt-1 text-gray-600 bg-gray-50 p-3 rounded-md">{{ $resultado->anexos_descricao }}</p>
                            </div>
                        @endif

                        {{-- Lista os ARQUIVOS ANEXADOS --}}
                        <div class="mt-4">
                            <p class="font-semibold text-gray-700">Arquivos Enviados:</p>

                            {{-- A diretiva @forelse é perfeita aqui: ela faz um loop se houver anexos,
                                ou mostra uma mensagem se a lista estiver vazia. --}}
                            @forelse ($resultado->anexos as $anexo)
                                <div class="mt-2 p-3 border rounded-lg flex items-center justify-between hover:bg-gray-50">
                                    {{-- Link para abrir o arquivo em uma nova aba --}}
                                    <a href="{{ Storage::url($anexo->path) }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $anexo->nome_original }}
                                    </a>
                                    {{-- Mostra o tipo do arquivo (Ex: image/jpeg) --}}
                                    <span class="text-sm text-gray-500">{{ $anexo->mime_type }}</span>
                                </div>
                            @empty
                                {{-- Esta mensagem aparece se nenhum anexo foi enviado --}}
                                <p class="mt-2 text-gray-600">Nenhum arquivo foi anexado a este relatório.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">PARECERES DA AVALIAÇÃO</h3>
                <div class="space-y-6">
                    
                    <div>
                        <h4 class="font-semibold">Parecer do NAPEX</h4>
                        @php
                            // Condição para mostrar o formulário: ser do perfil e a avaliação não estar finalizada
                            $podeAvaliarNapex = auth()->user()->role === 'napex' && !in_array($resultado->status, ['aprovado', 'reprovado']);
                        @endphp

                        @if($podeAvaliarNapex)
                            <form action="{{ route('resultados.avaliar', $resultado) }}" method="POST" class="mt-2">
                                @csrf
                                {{-- O campo é pré-preenchido com o parecer existente --}}
                                <textarea name="parecer" rows="4" class="w-full border-gray-300 rounded-md shadow-sm" required>{{ old('parecer', $resultado->parecer_napex) }}</textarea>
                                <div class="mt-2 flex items-center gap-4">
                                    {{-- O select é pré-selecionado com a decisão existente --}}
                                    <select name="aprovacao" class="border-gray-300 rounded-md shadow-sm" required>
                                        <option value="">-- Decisão --</option>
                                        <option value="sim" {{ $resultado->aprovado_napex == 'sim' ? 'selected' : '' }}>Aprovar</option>
                                        <option value="nao" {{ $resultado->aprovado_napex == 'nao' ? 'selected' : '' }}>Reprovar</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md text-xs uppercase hover:bg-blue-700">Salvar Parecer</button>
                                </div>
                            </form>
                        @else
                            <p class="mt-1 text-sm text-gray-700 border p-3 rounded-md bg-gray-50 whitespace-pre-wrap">{{ $resultado->parecer_napex ?? 'Aguardando avaliação.' }}</p>
                            @if($resultado->aprovado_napex !== 'pendente')
                               <p class="mt-2 text-sm"><strong>Status:</strong> <span class="{{ $resultado->aprovado_napex === 'sim' ? 'text-green-600' : 'text-red-600' }} font-bold">
                                    {{ $resultado->aprovado_napex === 'sim' ? 'Aprovado' : 'Não Aprovado' }}
                                </span></p>
                            @endif
                        @endif
                    </div>

                    <div>
                        <h4 class="font-semibold">Parecer da Coordenação</h4>
                        @php
                            $podeAvaliarCoord = auth()->user()->role === 'coordenador' && !in_array($resultado->status, ['aprovado', 'reprovado']);
                        @endphp

                        @if($podeAvaliarCoord)
                            <form action="{{ route('resultados.avaliar', $resultado) }}" method="POST" class="mt-2">
                                @csrf
                                <textarea name="parecer" rows="4" class="w-full border-gray-300 rounded-md shadow-sm" required>{{ old('parecer', $resultado->parecer_coordenador) }}</textarea>
                                <div class="mt-2 flex items-center gap-4">
                                    <select name="aprovacao" class="border-gray-300 rounded-md shadow-sm" required>
                                        <option value="">-- Decisão --</option>
                                        <option value="sim" {{ $resultado->aprovado_coordenador == 'sim' ? 'selected' : '' }}>Aprovar</option>
                                        <option value="nao" {{ $resultado->aprovado_coordenador == 'nao' ? 'selected' : '' }}>Reprovar</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md text-xs uppercase hover:bg-blue-700">Salvar Parecer</button>
                                </div>
                            </form>
                        @else
                            <p class="mt-1 text-sm text-gray-700 border p-3 rounded-md bg-gray-50 whitespace-pre-wrap">{{ $resultado->parecer_coordenador ?? 'Aguardando avaliação.' }}</p>
                            @if($resultado->aprovado_coordenador !== 'pendente')
                               <p class="mt-2 text-sm"><strong>Status:</strong> <span class="{{ $resultado->aprovado_coordenador === 'sim' ? 'text-green-600' : 'text-red-600' }} font-bold">
                                    {{ $resultado->aprovado_coordenador === 'sim' ? 'Aprovado' : 'Não Aprovado' }}
                                </span></p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @if ($resultado->rejeicoes->isNotEmpty())
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">HISTÓRICO DE REJEIÇÕES</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Avaliador</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Motivo da Rejeição</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($resultado->rejeicoes->sortByDesc('created_at') as $rejeicao)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $rejeicao->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $rejeicao->user->name }} ({{ ucfirst($rejeicao->user->role) }})</td>
                                        <td class="px-6 py-4 whitespace-pre-wrap">{{ $rejeicao->motivo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="mt-10"  id="historico">
                <div class="relative text-center mb-4">
                        {{-- Título Centralizado --}}
                        <h2 class="text-xl font-bold text-[#251C57]">Histórico Detalhado</h2>

                        {{-- Botão Menor e Posicionado à Direita --}}
                        <a href="{{ route('projetos.exportarLogPdf', $projeto) }}#historico" 
                        class="absolute top-0 right-0 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-md text-sm inline-flex items-center">
                            <svg class="fill-current w-3 h-3 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                            <span>Exportar</span>
                        </a>
                    </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full w-full border border-gray-300 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">
                                @php
                                    $nextSortDirection = ($sortDirection === 'desc') ? 'asc' : 'desc';
                                @endphp
                                
                                
                                <a href="{{ route('projetos.show', ['id' => $projeto->id, 'sort' => $nextSortDirection]) }}#historico" class="inline-flex items-center">
                                    Data
                                    @if ($sortDirection === 'desc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                </a>
                            </th>
                            <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Usuário</th>
                            <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Origem</th>
                            <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Ação</th>
                            <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Descrição</th>
                        </tr>
                    </thead>
                        <tbody>
                            @if ($projeto && $logs->isNotEmpty())
                                @foreach ($logs->groupBy('batch_id') as $batchId => $batch)
                                    @php
                                        // Se o batch_id for nulo ou vazio, não é um grupo de verdade
                                        $isGroup = !empty($batchId) && $batch->count() > 1;
                                    @endphp

                                    @foreach ($batch as $log)
                                        @if ($loop->first)
                                            <tr class="hover:bg-gray-50 @if($isGroup) border-l-4 border-blue-500 @endif">
                                                <td class="py-2 px-3 border-b align-top" @if($isGroup) rowspan="{{ $batch->count() }}" @endif>
                                                    {{ $log->created_at->format('d/m/Y') }}
                                                    <span class="block text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                                                </td>
                                                <td class="py-2 px-3 border-b align-top" @if($isGroup) rowspan="{{ $batch->count() }}" @endif>
                                                    {{ $log->user->name ?? 'Sistema' }}
                                                </td>
                                                <td class="py-2 px-3 border-b">
                                                    @if (str_contains($log->loggable_type, 'Projeto'))
                                                        <span class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">Proposta</span>
                                                    @elseif (str_contains($log->loggable_type, 'Resultado'))
                                                        <span class="px-2 py-1 font-semibold leading-tight text-purple-700 bg-purple-100 rounded-full">Relatório</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-3 border-b">{{ $log->acao }}</td>
                                                <td class="py-2 px-3 border-b">{{ $log->descricao }}</td>
                                            </tr>
                                        @else
                                            <tr class="hover:bg-gray-50 border-l-4 border-blue-500">
                                                <td class="py-2 px-3 border-b">
                                                    @if (str_contains($log->loggable_type, 'Projeto'))
                                                        <span class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">Proposta</span>
                                                    @elseif (str_contains($log->loggable_type, 'Resultado'))
                                                        <span class="px-2 py-1 font-semibold leading-tight text-purple-700 bg-purple-100 rounded-full">Relatório</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-3 border-b">{{ $log->acao }}</td>
                                                <td class="py-2 px-3 border-b">{{ $log->descricao }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">Nenhum histórico de alterações encontrado.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

    </div>
</x-app-layout>