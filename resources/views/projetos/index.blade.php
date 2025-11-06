@php use Illuminate\Support\Str; @endphp

<x-app-layout>
<x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Propostas de Projeto Extensionista - Curricularização da Extensão') }}
        </h2>
    </x-slot>

    <x-slot name="pageTitle">
        Propostas e Relatórios de Atividade Extensionista Curricularização da Extensão
    </x-slot>



    <div class="pt-1 pb-10">
        <div class="w-full mx-auto sm:px-5 lg:px-0">
       
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="flex justify-between items-center flex-wrap mb-6 gap-2">

                    <div class="flex items-center gap-2 flex-wrap">
                        <button id="btn-filtro"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('projetos.index') }}"
                            class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm">
                            <img src="{{ asset('img/site/btn-limpar.png') }}" alt="Limpar" width="18" height="18" class="self-center">
                            Limpar Filtros
                        </a>

                        <div class="flex items-center gap-2">
                            <label for="ordenar" class="text-sm fw-bold mb-0">Ordenar por:</label>
                            <form method="GET" action="{{ route('projetos.index') }}" id="form-ordenar">
                                {{-- Adicionado: Campos ocultos para manter os filtros atuais --}}
                                @foreach (request()->except(['ordenar', 'page']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach

                                <select name="ordenar" id="ordenar" class="form-select form-select-sm h-[36px] text-sm" onchange="this.form.submit()">
                                    <option value="">Selecione</option>
                                    <option value="data_asc" {{ request('ordenar') == 'data_asc' ? 'selected' : '' }}>📅 Data de criação ↑</option>
                                    <option value="data_desc" {{ request('ordenar') == 'data_desc' ? 'selected' : '' }}>📅 Data de criação ↓(Mais Novos)</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    @if (in_array(auth()->user()->role, ['napex', 'coordenador']))
                        <a href="{{ route('projetos.exportarPdf', request()->query()) }}"
                            class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white font-bold px-4 h-[36px] rounded text-sm">
                            📄 EXPORTAR TABELA EM PDF
                        </a>
                    @endif
                                @can('create', App\Models\Projeto::class)
                <a href="{{ route('projetos.create') }}"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm">
                    ＋ Nova Proposta
                </a>
            @endcan
        </div>

                <div id="filtro-box" style="display: none;" class="bg-gray-50 p-4 rounded-lg mb-8">
                    @if ($errors->any())
                        <div class="mb-4 text-red-600">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('projetos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <div>
                            <label class="block mb-1">Etapa:</label>
                            <select name="etapa" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todas --</option>
                                <option value="Proposta" {{ request('etapa') === 'Proposta' ? 'selected' : '' }}>Proposta</option>
                                <option value="Resultado" {{ request('etapa') === 'Resultado' ? 'selected' : '' }}>Resultado</option>
                                <option value="Concluído" {{ request('etapa') === 'Concluído' ? 'selected' : '' }}>Concluído</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Título:</label>
                            <input type="text" name="titulo" value="{{ request('titulo') }}" class="w-full border-gray-300 rounded-md py-0.5">
                        </div>

                        @if(in_array(auth()->user()->role, ['coordenador', 'napex','admin']))
                        <div>
                            <label for="curso_id" class="block mb-1">Curso:</label>
                            <select name="curso_id" id="curso_id" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                @foreach($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-span-2">
                            <label class="block mb-1">Data Início:</label>
                            <div class="flex gap-2">
                                <input type="date" name="data_inicio_de" value="{{ request('data_inicio_de') }}" class="w-full border-gray-300 rounded-md py-0.5">
                                <span class="self-center">até</span>
                                <input type="date" name="data_inicio_ate" value="{{ request('data_inicio_ate') }}" class="w-full border-gray-300 rounded-md py-0.5">
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="block mb-1">Data Fim:</label>
                            <div class="flex gap-2">
                                <input type="date" name="data_fim_de" value="{{ request('data_fim_de') }}" class="w-full border-gray-300 rounded-md py-0.5">
                                <span class="self-center">até</span>
                                <input type="date" name="data_fim_ate" value="{{ request('data_fim_ate') }}" class="w-full border-gray-300 rounded-md py-0.5">
                            </div>
                        </div>


                        @php
                            $role = auth()->user()->role;
                        @endphp

                        <div>
                            <label for="status" class="block mb-1">Status:</label>
                            <select name="status" id="status" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                
                                <optgroup label="Proposta & Resultado">
                                    <option value="editando" {{ request('status') == 'editando' ? 'selected' : '' }}>Editando</option>
                                    <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Entregue</option>
                                    <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                    <option value="reprovado" {{ request('status') == 'reprovado' ? 'selected' : '' }}>Reprovado</option>
                                    <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </optgroup>
                            </select>
                        </div>


                        <div>
                            <label class="block mb-1">Aprovação NAPEx:</label>
                            <select name="aprovado_napex" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovado_napex') === 'sim' ? 'selected' : '' }}>Sim</option>
                                <option value="nao" {{ request('aprovado_napex') === 'nao' ? 'selected' : '' }}>Não</option>
                                <option value="pendente" {{ request('aprovado_napex') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Aprovação Coordenador:</label>
                            <select name="aprovado_coordenador" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovado_coordenador') === 'sim' ? 'selected' : '' }}>Sim</option>
                                <option value="nao" {{ request('aprovado_coordenador') === 'nao' ? 'selected' : '' }}>Não</option>
                                <option value="pendente" {{ request('aprovado_coordenador') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Pesquisar</button>
                        </div>
                    </form>


                </div>
{{-- =================================== --}}
{{-- ==== FORMULÁRIO DE PDF EM LOTE ==== --}}
{{-- =================================== --}}
@if(in_array(auth()->user()->role, ['admin', 'napex']) || str_starts_with(auth()->user()->role, 'coordenador'))
    
    @php
        // Pega o limite definido no Controller
        $projetoCount = $projetos->count();
        $excedeLimite = $projetoCount > $pdfLimit;
    @endphp

    <div class="mb-4 p-4 border rounded-lg bg-gray-50">
        <form action="{{ route('projetos.gerarPdfEmLote') }}" method="POST"
              onsubmit="return confirm('Isso irá gerar um .zip com {{ $projetoCount }} PDF(s) com base nos filtros atuais. Deseja continuar?')">
            @csrf
            
            {{-- Passa todos os IDs dos projetos atualmente filtrados --}}
            @foreach ($projetos as $projeto)
                <input type="hidden" name="projeto_ids[]" value="{{ $projeto->id }}">
            @endforeach

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" 
                        @if($excedeLimite || $projetoCount === 0) disabled @endif
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-semibold text-xs uppercase rounded-md hover:bg-red-700
                               disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Gerar PDF do Lote ({{ $projetoCount }} projetos)
                </button>
                
                @if($excedeLimite)
                    <p class="text-sm text-red-600 font-semibold">
                        Limite de {{ $pdfLimit }} PDFs por lote excedido. Por favor, refine seus filtros.
                    </p>
                @elseif($projetoCount === 0)
                     <p class="text-sm text-gray-500">
                        Nenhum projeto encontrado para gerar PDFs.
                    </p>
                @else
                    <p class="text-sm text-gray-600">
                        Isso irá gerar um .zip com as propostas ou relatórios dos {{ $projetoCount }} projetos listados.
                    </p>
                @endif
            </div>
        </form>
    </div>
@endif
{{-- =================================== --}}   
                <div class="overflow-x-auto">
                    <table class="min-w-full w-full max-w-7xl bg-white border border-gray-300 rounded-lg">
                        <thead>

                            <tr class="bg-[#251C57] text-white">
                                <th class="py-1 px-4 text-left">#</th>
                                <th class="py-1 px-4 text-center">Título</th>
                                <th class="py-1 px-4 text-center">Curso</th>
                                <th class="py-1 px-4 text-center">Data Início</th>
                                <th class="py-1 px-4 text-center">Data Fim</th>
                                <th class="py-1 px-4 text-center">Aprovação NAPEx</th>
                                <th class="py-1 px-4 text-center">Aprovação Coordenador</th>
                                <th class="py-1 px-4 text-center">Etapa</th>
                                <th class="py-1 px-4 text-center">Status</th> 
                                <th class="py-1 px-4 text-center" colspan="2">Detalhes/Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            
                            @foreach ($projetos as $index => $projeto)

                                <tr class="hover:bg-gray-100">
                                    <td class="py-2 px-6 text-center">{{ ($projetos->currentPage() - 1) * $projetos->perPage() + $index + 1 }}</td>


                                    <td class="py-2 px-6 text-center" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                        {{ $projeto->titulo }}
                                    </td>

                                    <td class="py-2 px-6 text-center" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                            {{ $projeto->user->curso->nome_resumido ?? 'N/D' }}
                                    </td>


                                    <td class="py-2 px-6 text-center" >{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>

                                    <td class="py-2 px-6 text-center">{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>


                                    <td class="py-2 px-6 text-center" style="max-width: 50px;">
                                        @php
                                            $aprovacao = 'N/A'; // Define um valor padrão
                                            if ($projeto->etapa === 'Proposta') {
                                                // Se a etapa for Proposta, busca a aprovação da tabela 'projetos'
                                                $aprovacao = $projeto->aprovado_napex;
                                            } elseif ($projeto->etapa === 'Resultado' && $projeto->resultado) {
                                                // Se a etapa for Relatório, busca a aprovação da tabela 'relatório'
                                                $aprovacao = $projeto->resultado->aprovado_napex;
                                            } elseif ($projeto->etapa === 'Concluído') {
                                                // Se estiver concluído, a aprovação é sempre 'Sim'
                                                $aprovacao = 'sim';
                                            }
                                        @endphp
                                        {{-- A lógica de exibição permanece a mesma, mas usando a variável inteligente --}}
                                        {{ $aprovacao === 'sim' ? 'Sim' : ($aprovacao === 'nao' ? 'Não' : 'Pendente') }}
                                    </td>

                                    <td class="py-2 px-6 text-center" style="max-width: 50px;">
                                        @php
                                            $aprovacao = 'N/A'; // Define um valor padrão
                                            if ($projeto->etapa === 'Proposta') {
                                                $aprovacao = $projeto->aprovado_coordenador;
                                            } elseif ($projeto->etapa === 'Resultado' && $projeto->resultado) {
                                                $aprovacao = $projeto->resultado->aprovado_coordenador;
                                            } elseif ($projeto->etapa === 'Concluído') {
                                                $aprovacao = 'sim';
                                            }
                                        @endphp
                                        {{ $aprovacao === 'sim' ? 'Sim' : ($aprovacao === 'nao' ? 'Não' : 'Pendente') }}
                                    </td>

                                    <td class="py-2 px-6 font-bold text-center">
                                        {{ $projeto->etapa }}
                                    </td>

                                    @php
                                        $status = '';
                                        $cor = 'text-gray-700'; // Cor padrão

                                        if ($projeto->etapa === 'Proposta') {
                                            $status = $projeto->status;
                                            $cor = match($status) {
                                                'editando' => 'text-yellow-800',
                                                'entregue' => 'text-blue-800',
                                                'aprovado' => 'text-green-800',
                                                'reprovado' => 'text-red-800',
                                                default => 'text-gray-700'
                                            };
                                        } elseif ($projeto->etapa === 'Resultado') {
                                            // Se o relatório de resultado já existe, pegue o status dele
                                            if ($projeto->resultado) {
                                                $status = $projeto->resultado->status;
                                                $cor = match($status) {
                                                    'editando' => 'text-yellow-800',
                                                    'entregue' => 'text-blue-800',
                                                    'aprovado' => 'text-green-800',
                                                    'reprovado' => 'text-red-800',
                                                    default => 'text-gray-700'
                                                };
                                            } else {
                                                // Se o relatório AINDA NÃO EXISTE, o status da proposta é 'Aprovado'.
                                                $status = 'Aprovado';
                                                $cor = 'text-green-800'; // Cor para 'Aprovado'
                                            }
                                        } elseif ($projeto->etapa === 'Concluído') {
                                            $status = 'Finalizado';
                                            $cor = 'text-green-800 font-bold';
                                        }
                                    @endphp
                                    <td class="py-2 px-6 text-center {{ $cor }}" style="max-width: 30px;">
                                        {{ ucfirst($status) }}
                                    </td>
  

                                    {{-- ======================= CÉLULA DE AÇÕES  ======================= --}}
                                    <td class="py-2 px-6" style="min-width: 200px;" x-data="{ openModal: false }">
                                        <div class="flex items-center justify-start gap-2 flex-nowrap">

                                            @php
                                                $baseBtnClass = 'inline-flex justify-center font-bold py-1 px-2 rounded text-sm whitespace-nowrap';
                                                $user = auth()->user();
                                                $canEvaluate = false;

                                                // A proposta só pode ser avaliada se estiver 'entregue'
                                                if ($projeto->status === 'entregue') {
                                                    if ($user->can('approveByNapex', $projeto) || $user->can('approveByCoordinator', $projeto)) {
                                                        $canEvaluate = true;
                                                    }
                                                }
                                            @endphp

                                            @if ($canEvaluate)
                                                {{-- BOTÃO DE AVALIAR PROPOSTA (Avaliadores) --}}
                                                <a href="{{ route('projetos.show', $projeto->id) }}" title="Avaliar Proposta" class="{{ $baseBtnClass }} bg-green-600 hover:bg-green-700 text-white">Avaliar</a>
                                            @else
                                                {{-- BOTÃO DE VISUALIZAR (Padrão para todos os outros casos) --}}
                                                <a href="{{ route('projetos.show', $projeto->id) }}" title="Visualizar Proposta" class="{{ $baseBtnClass }} bg-blue-600 hover:bg-blue-700 text-white">Ver Proposta</a>
                                            @endif

                                            {{-- BOTÃO DE EDITAR PROPOSTA --}}
                                            @can('update', $projeto)
                                                <a href="{{ route('projetos.edit', $projeto->id) }}" title="Editar Proposta" class="{{ $baseBtnClass }} bg-yellow-600 hover:bg-yellow-700 text-white">Editar</a>
                                            @endcan

                                            {{-- BOTÃO DE APAGAR PROPOSTA (Apenas proponente) --}}
                                            @can('delete', $projeto)
                                                <button @click="openModal = true" title="Apagar Proposta" class="{{ $baseBtnClass }} bg-red-600 hover:bg-red-700 text-white">Apagar</button>
                                            @endcan


                                            {{-- --- LÓGICA PARA RELATÓRIO --- --}}
                                                @if ($projeto->resultado)
                                                    @can('evaluate', $projeto->resultado)
                                                        {{-- Se o usuário pode AVALIAR, mostra somente o botão de avaliar --}}
                                                        <a href="{{ route('resultados.show', $projeto->resultado) }}" class="{{ $baseBtnClass }} bg-green-600 hover:bg-green-700 text-white" title="Avaliar Relatório">Avaliar</a>
                                                    @elsecan('view', $projeto->resultado)
                                                        {{-- Senão, se ele pode apenas VISUALIZAR, mostra o botão de ver --}}
                                                        <a href="{{ route('resultados.show', $projeto->resultado) }}" title="Visualizar Relatório" class="{{ $baseBtnClass }} bg-cyan-600 hover:bg-cyan-700 text-white">Ver Resultado</a>
                                                    @endcan

                                                    {{-- O botão de editar continua com sua própria lógica, sem interferir --}}
                                                    @can('update', $projeto->resultado)
                                                        <a href="{{ route('resultados.edit', $projeto->resultado) }}" title="Editar Relatório" class="{{ $baseBtnClass }} bg-yellow-600 hover:bg-yellow-700 text-white font-bold">Editar Relatório</a>
                                                    @endcan

                                                @else
                                                    
                                                    @if ($projeto->etapa === 'Proposta' && $projeto->status === 'aprovado')
                                                        @if (auth()->user()->role === 'aluno')
                                                            @can('create', [\App\Models\Resultado::class, $projeto])
                                                                <a href="{{ route('resultados.create', $projeto) }}" class="{{ $baseBtnClass }} bg-green-600 hover:bg-green-700 text-white">Add Relatório</a>
                                                            @endcan
                                                        @endif
                                                    @endif
                                                @endif


                                            {{-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO --}}
                                            <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                                <div class="bg-white rounded-lg p-6 shadow-lg w-80">
                                                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirmação</h2>
                                                    <p class="mb-6 text-gray-600">Tem certeza que deseja apagar este projeto?</p>
                                                    <div class="flex justify-end space-x-2">
                                                        <button @click="openModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-4 rounded">
                                                            Cancelar
                                                        </button>
                                                        <form action="{{ route('projetos.destroy', $projeto->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-4 rounded">
                                                                Apagar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                        @php
                                            $canGenerateCombined = $projeto->etapa === 'Concluído' && $projeto->status === 'aprovado';
                                        @endphp

                                        <div class="flex space-x-2">
                                            {{-- Botão Gerar PDFs (visível apenas sob condição) --}}
                                            @if ($canGenerateCombined)
                                                <a href="{{ route('projetos.downloadCompleto', $projeto->id) }}" 
                                                class="text-sm px-2 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 font-semibold"
                                                title="Gera ZIP com PDF da Proposta e Relatório Final">
                                                    Gerar PDFs
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $projetos->links() }}
                    </div>

                </div>

                <script>
                    const btnFiltro = document.getElementById('btn-filtro');
                    const filtroBox = document.getElementById('filtro-box');
                    btnFiltro.addEventListener('click', () => {
                        filtroBox.style.display = filtroBox.style.display === 'none' ? 'block' : 'none';
                    });
                </script>

            </div>
        </div>
    </div>
</x-app-layout>