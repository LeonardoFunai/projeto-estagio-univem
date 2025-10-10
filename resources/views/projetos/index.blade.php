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

            <!-- Erros -->
                @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                
                <!-- Botões -->
                <div class="flex justify-between items-center flex-wrap mb-6 gap-2">

                    <!-- Botões à esquerda -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Filtrar -->
                        <button id="btn-filtro"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm">
                            🔍 Filtrar
                        </button>

                        <!-- Limpar -->
                        <a href="{{ route('projetos.index') }}"
                            class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm">
                            <img src="{{ asset('img/site/btn-limpar.png') }}" alt="Limpar" width="18" height="18" class="self-center">
                            Limpar Filtros
                        </a>

                        <!-- Ordenar -->
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

                    <!-- Botão Gerar PDF à direita -->
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

                    <!-- Filtro -->
                    <form method="GET" action="{{ route('projetos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <!-- Cadastrado por
                         <input type="hidden" name="ordenar" value="{{ request('ordenar') }}">
                        <div>
                            <label class="block mb-1">Cadastrado por:</label>
                            <input type="text" name="cadastrado_por" value="{{ request('cadastrado_por') }}" class="w-full border-gray-300 rounded-md py-0.5">
                        </div> -->
                        
                        <!-- Etapa -->
                        <div>
                            <label class="block mb-1">Etapa:</label>
                            <select name="etapa" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todas --</option>
                                <option value="Proposta" {{ request('etapa') === 'Proposta' ? 'selected' : '' }}>Proposta</option>
                                <option value="Resultado" {{ request('etapa') === 'Resultado' ? 'selected' : '' }}>Resultado</option>
                                <option value="Concluído" {{ request('etapa') === 'Concluído' ? 'selected' : '' }}>Concluído</option>
                            </select>
                        </div>

                        <!-- Título -->
                        <div>
                            <label class="block mb-1">Título:</label>
                            <input type="text" name="titulo" value="{{ request('titulo') }}" class="w-full border-gray-300 rounded-md py-0.5">
                        </div>

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

                        <!-- Data Início de/até -->
                        <div class="col-span-2">
                            <label class="block mb-1">Data Início:</label>
                            <div class="flex gap-2">
                                <input type="date" name="data_inicio_de" value="{{ request('data_inicio_de') }}" class="w-full border-gray-300 rounded-md py-0.5">
                                <span class="self-center">até</span>
                                <input type="date" name="data_inicio_ate" value="{{ request('data_inicio_ate') }}" class="w-full border-gray-300 rounded-md py-0.5">
                            </div>
                        </div>

                        <!-- Data Fim de/até -->
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

                        <!-- Status -->
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


                        <!-- Aprovações -->
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

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full max-w-7xl bg-white border border-gray-300 rounded-lg">
                        <thead>

                            <!-- Colunas -->
                            <tr class="bg-[#251C57] text-white">
                                <th class="py-1 px-4 text-left">#</th>
                                <!-- <th class="py-1 px-4 text-left">Cadastrado por</th> -->
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


                                    <!-- Nome do perfil de cadastro
                                    <td class="py-2 px-6" style="max-width: 200px; word-wrap: break-word;">
                                        {{ Str::limit($projeto->user->name ?? 'Desconhecido', 50, '...') }}
                                    </td> -->


                                    <!-- Título -->
                                    <td class="py-2 px-6 text-center" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                        {{ $projeto->titulo }}
                                    </td>

                                    <!-- Curso -->
                                    <td class="py-2 px-6 text-center" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                            {{ $projeto->user->curso->nome_resumido ?? 'N/D' }}
                                    </td>


                                    <!-- Dat Início -->
                                    <td class="py-2 px-6 text-center" >{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>

                                    <!-- Data Fim -->
                                    <td class="py-2 px-6 text-center">{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>


                                    <!-- Aprovação Napex -->
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

                                    <!-- Aprovação Coord -->
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

                                    <!-- Etapa -->
                                    <td class="py-2 px-6 font-bold text-center">
                                        {{ $projeto->etapa }}
                                    </td>

                                    <!-- status -->
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
  

                                    <!-- Ações -->
                                    {{-- ======================= CÉLULA DE AÇÕES CORRIGIDA ======================= --}}
                                    <td class="py-2 px-6" style="min-width: 200px;" x-data="{ openModal: false }">
                                        <div class="flex items-center justify-start gap-2 flex-nowrap">

                                            @php
                                                $baseBtnClass = 'inline-flex justify-center font-bold py-1 px-2 rounded text-sm whitespace-nowrap';
                                            @endphp

                                            {{-- BOTÃO DE VISUALIZAR (Sempre visível se tiver acesso ao projeto) --}}
                                            <a href="{{ route('projetos.show', $projeto->id) }}" title="Visualizar Proposta" class="{{ $baseBtnClass }} bg-blue-600 hover:bg-blue-700 text-white">Ver Proposta</a>

                                            {{-- BOTÃO DE EDITAR PROPOSTA --}}
                                            @can('update', $projeto)
                                                <a href="{{ route('projetos.edit', $projeto->id) }}" title="Editar Proposta" class="{{ $baseBtnClass }} bg-yellow-600 hover:bg-yellow-700 text-white">Editar</a>
                                            @endcan

                                            {{-- BOTÃO DE APAGAR PROPOSTA (Apenas proponente) --}}
                                            @can('delete', $projeto)
                                                <button @click="openModal = true" title="Apagar Proposta" class="{{ $baseBtnClass }} bg-red-600 hover:bg-red-700 text-white">Apagar</button>
                                            @endcan

                                            {{-- BOTÃO DE AVALIAR PROPOSTA (Avaliadores) --}}
                                            @can('avaliar', $projeto)
                                                <a href="{{ route('projetos.show', $projeto->id) }}" class="{{ $baseBtnClass }} bg-green-600 hover:bg-green-700 text-white" title="Analisar Proposta">Avaliar Proposta</a>
                                            @endcan


                                            {{-- --- LÓGICA PARA RELATÓRIO --- --}}
                                            @if ($projeto->resultado)
                                                {{-- Se o relatório já existe --}}
                                                <a href="{{ route('resultados.show', $projeto->resultado) }}" title="Visualizar Relatório" class="{{ $baseBtnClass }} bg-cyan-600 hover:bg-cyan-700 text-white">Ver Resultado</a>

                                                {{-- BOTÃO DE EDITAR RELATÓRIO --}}
                                                @can('update', $projeto->resultado)
                                                    <a href="{{ route('resultados.edit', $projeto->resultado) }}" title="Editar Relatório" class="{{ $baseBtnClass }} bg-yellow-600 hover:bg-yellow-700 text-white font-bold">Editar Relatório</a>
                                                @endcan
                                                
                                                {{-- BOTÃO DE AVALIAR RELATÓRIO (Avaliadores) --}}
                                                @can('avaliar', $projeto->resultado)
                                                    <a href="{{ route('resultados.show', $projeto->resultado) }}" class="{{ $baseBtnClass }} bg-green-600 hover:bg-green-700 text-white" title="Analisar Relatório">Avaliar Resultado</a>
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $projetos->links() }}
                    </div>

                </div>

                <!-- Comportamento do Filtro -->
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
