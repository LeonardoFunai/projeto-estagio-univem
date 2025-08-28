@php use Illuminate\Support\Str; @endphp
<x-app-layout>
<x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Propostas de Projeto Extensionista - Curricularização da Extensão') }}
        </h2>
    </x-slot>

    <x-slot name="pageTitle">
        Lista de Proposta de Atividade Extensionista Curricularização da Extensão
    </x-slot>



    <div class="pt-1 pb-10">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
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
                            📄 Gerar Relatório em PDF
                        </a>
                    @endif

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
                        <!-- Cadastrado por -->
                         <input type="hidden" name="ordenar" value="{{ request('ordenar') }}">
                        <div>
                            <label class="block mb-1">Cadastrado por:</label>
                            <input type="text" name="cadastrado_por" value="{{ request('cadastrado_por') }}" class="w-full border-gray-300 rounded-md py-0.5">
                        </div>
                        
                        <!-- Etapa -->
                        <div>
                            <label class="block mb-1">Etapa:</label>
                            <select name="etapa" class="w-auto border-gray-300 rounded-md py-1">
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
                            <label class="block mb-1">Status:</label>
                            <select name="status" class="w-auto border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                @if ($role === 'aluno' || $role === 'professor')
                                    <option value="editando" {{ request('status') === 'editando' ? 'selected' : '' }}>Editando</option>
                                @endif
                                <option value="entregue" {{ request('status') === 'entregue' ? 'selected' : '' }}>Entregue</option>
                                <option value="aprovado" {{ request('status') === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                            </select>
                        </div>


                        <!-- Aprovações -->
                        <div>
                            <label class="block mb-1">Aprovação NAPEx:</label>
                            <select name="aprovado_napex" class="w-auto border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovado_napex') === 'sim' ? 'selected' : '' }}>Sim</option>
                                <option value="nao" {{ request('aprovado_napex') === 'nao' ? 'selected' : '' }}>Não</option>
                                <option value="pendente" {{ request('aprovado_napex') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Aprovação Coordenador:</label>
                            <select name="aprovado_coordenador" class="w-auto border-gray-300 rounded-md py-1">
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
                                <th class="py-1 px-4 text-left">Cadastrado por</th>
                                <th class="py-1 px-4 text-left">Título</th>
                                <th class="py-1 px-4 text-left">Data Início</th>
                                <th class="py-1 px-4 text-left">Data Fim</th>
                                <th class="py-1 px-4 text-left">Etapa</th>
                                <th class="py-1 px-4 text-left">Aprovação NAPEx</th>
                                <th class="py-1 px-4 text-left">Aprovação Coordenador</th>
                                <th class="py-1 px-4 text-left">Status</th> 
                                <th class="py-1 px-4 text-left">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            
                            @foreach ($projetos as $index => $projeto)

                                <tr class="hover:bg-gray-100">
                                    <td class="py-2 px-6">{{ ($projetos->currentPage() - 1) * $projetos->perPage() + $index + 1 }}</td>


                                    <!-- Nome do perfil de cadastro -->
                                    <td class="py-2 px-6" style="max-width: 200px; word-wrap: break-word;">
                                        {{ Str::limit($projeto->user->name ?? 'Desconhecido', 50, '...') }}
                                    </td>


                                    <!-- Título -->
                                    <td class="py-2 px-6" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                        {{ $projeto->titulo }}
                                    </td>


                                    <!-- Dat Início -->
                                    <td class="py-2 px-6" >{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>

                                    <!-- Data Fim -->
                                    <td class="py-2 px-6">{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>

                                    <!-- Etapa -->
                                    <td class="py-2 px-6 font-bold">
                                        {{ $projeto->etapa }}
                                    </td>

                                    <!-- Aprovação Napex -->
                                    <td class="py-2 px-6" style="max-width: 50px;">
                                        @php
                                            $aprovacao = 'N/A'; // Define um valor padrão
                                            if ($projeto->etapa === 'Proposta') {
                                                // Se a etapa for Proposta, busca a aprovação da tabela 'projetos'
                                                $aprovacao = $projeto->aprovado_napex;
                                            } elseif ($projeto->etapa === 'Resultado' && $projeto->resultado) {
                                                // Se a etapa for Resultado, busca a aprovação da tabela 'resultados'
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
                                    <td class="py-2 px-6" style="max-width: 50px;">
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
                                                'reprovado' => 'text-red-800', // Adicionando caso de reprovado
                                                default => 'text-gray-700'
                                            };
                                        } elseif ($projeto->etapa === 'Resultado' && $projeto->resultado) {
                                            $status = $projeto->resultado->status;
                                            $cor = match($status) {
                                                'rascunho' => 'text-yellow-800',
                                                'enviado' => 'text-blue-800',
                                                'aprovado' => 'text-green-800',
                                                'reprovado' => 'text-red-800',
                                                default => 'text-gray-700'
                                            };
                                        } elseif ($projeto->etapa === 'Concluído') {
                                            $status = 'Finalizado';
                                            $cor = 'text-green-800 font-bold';
                                        }
                                    @endphp
                                    <td class="py-2 px-6 {{ $cor }}" style="max-width: 30px;">
                                        {{ ucfirst($status) }}
                                    </td>
  

                                    <!-- Ações -->
                                    <td class="py-2 px-6" style="min-width: 100px;" x-data="{ openModal: false }">
                                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap:nowrap">

                                            @php
                                                // Identifica o papel do usuário (sua lógica original)
                                                $role = auth()->user()->role;
                                                $isAluno = $role === 'aluno';
                                                $isProfessor = $role === 'professor';
                                                $isNapexOrCoord = in_array($role, ['napex', 'coordenador']);
                                            @endphp

                                            {{-- ======================= ETAPA PROPOSTA ======================= --}}
                                            @if ($projeto->etapa === 'Proposta')
                                                @php
                                                    // Condições específicas da ETAPA PROPOSTA (sua lógica original)
                                                    $podeEditar = $projeto->status === 'editando';
                                                    $podeVoltar = $projeto->status === 'entregue' &&
                                                                Str::lower(trim($projeto->aprovado_napex ?? 'pendente')) === 'pendente' &&
                                                                Str::lower(trim($projeto->aprovado_coordenador ?? 'pendente')) === 'pendente';
                                                    $podeAprovar = $projeto->status === 'entregue';
                                                @endphp

                                                <a href="{{ route('projetos.show', $projeto->id) }}" title="Visualizar Proposta">
                                                    <img src="{{ asset('img/site/btn-visualizar.png') }}" alt="Visualizar" width="26">
                                                </a>

                                                @if (($isAluno || $isProfessor) && $podeEditar)
                                                    <a href="{{ route('projetos.edit', $projeto->id) }}" title="Editar Proposta">
                                                        <img src="{{ asset('img/site/btn-editar.png') }}" alt="Editar" width="26">
                                                    </a>
                                                @endif
                                                
                                                @if ($isAluno && $podeEditar)
                                                    <button @click="openModal = true" class="text-red-600 hover:underline">
                                                        <img src="{{ asset('img/site/btn-apagar.png') }}" title="Apagar" alt="Apagar" width="24">
                                                    </button>
                                                    {{-- Seu modal de confirmação continua aqui --}}
                                                @endif

                                                @if (($isAluno || $isProfessor) && $podeVoltar)
                                                    <form action="{{ route('projetos.voltar', $projeto->id) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="text-yellow-600 hover:underline">
                                                            <img src="{{ asset('img/site/btn-voltar-editar.png') }}" title="Voltar para Edição" alt="Voltar" width="24">
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if ($isNapexOrCoord && $podeAprovar)
                                                    <a href="{{ route('projetos.show', $projeto->id) }}" class="text-green-700 hover:underline font-semibold">Análise/Parecer</a>
                                                @endif


                                            {{-- ======================= ETAPA RESULTADO ======================= --}}
                                            @elseif ($projeto->etapa === 'Resultado')
                                                        
                                                        <a href="{{ route('projetos.show', $projeto->id) }}" title="Visualizar Proposta Original"><img src="{{ asset('img/site/btn-visualizar.png') }}" alt="Visualizar Proposta" width="26"></a>

                                                        @if ($projeto->resultado)
                                                            @php
                                                                // Condições da ETAPA RESULTADO
                                                                $resultadoEnviado = $projeto->resultado->status === 'enviado';
                                                                // CORREÇÃO AQUI: Aluno pode editar se estiver como rascunho OU reprovado
                                                                $podeEditarResultado = in_array($projeto->resultado->status, ['rascunho', 'reprovado']);
                                                            @endphp

                                                            <a href="{{ route('resultados.show', $projeto->resultado) }}" title="Visualizar Resultado" class="text-blue-600 hover:underline font-semibold whitespace-nowrap">Ver Resultado</a>

                                                            @if ($isAluno && $podeEditarResultado)
                                                                <a href="{{ route('resultados.edit', $projeto->resultado) }}" title="Editar Resultado" class="text-green-700 hover:underline font-semibold whitespace-nowrap">Editar Resultado</a>
                                                            @endif
                                                            
                                                            @if ($isNapexOrCoord && $resultadoEnviado)
                                                                <a href="{{ route('resultados.show', $projeto->resultado) }}" class="text-green-700 hover:underline font-semibold">Análise/Parecer</a>
                                                            @endif
                                                        @else
                                                            @if ($isAluno)
                                                                <a href="{{ route('resultados.create', $projeto) }}" class="text-green-700 hover:underline font-bold whitespace-nowrap">Adicionar Resultado</a>
                                                            @endif
                                                        @endif

                                            {{-- ======================= ETAPA CONCLUÍDO ======================= --}}
                                            @elseif ($projeto->etapa === 'Concluído')
                                                <a href="{{ route('projetos.show', $projeto->id) }}" title="Visualizar Proposta">
                                                    <img src="{{ asset('img/site/btn-visualizar.png') }}" alt="Visualizar Proposta" width="26">
                                                </a>
                                                <a href="{{ route('resultados.show', $projeto->resultado) }}" title="Visualizar Resultado" class="text-blue-600 hover:underline font-semibold whitespace-nowrap">Ver Resultado</a>
                                            @endif
                                            
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
