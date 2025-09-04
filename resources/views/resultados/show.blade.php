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
                $podeEditarOuEnviar = in_array($resultado->status, ['rascunho', 'reprovado']);
                
                $podeVoltar = $resultado->status === 'enviado' && 
                              $resultado->aprovado_napex === 'pendente' && 
                              $resultado->aprovado_coordenador === 'pendente';
            @endphp

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center space-x-4">

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
                    <a href="{{ route('resultados.edit', $resultado) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Editar Relatório
                    </a>
                    <!-- Botão de Enviar -->
                    <form action="{{ route('resultados.enviar', $resultado) }}" method="POST" onsubmit="return confirm('Você tem certeza que deseja enviar o relatório para avaliação? Você poderá retorná-lo para o modo de edição caso ainda não tenha sido avaliado.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Enviar para Avaliação
                        </button>
                    </form>
                @endif
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
                <h3 class="text-lg font-bold text-gray-800 mb-4">RELATÓRIO DE RESULTADOS ENVIADO</h3>
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
        </div>
    </div>
</x-app-layout>