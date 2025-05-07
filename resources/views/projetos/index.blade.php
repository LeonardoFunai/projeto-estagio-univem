<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Propostas de Projeto Extensionista - Curricularização da Extensão') }}
        </h2>
    </x-slot>

    <h1 class="text-2xl font-bold text-blue-800 text-center mb-6">
        Lista de Proposta de Atividade Extensionista Curricularização da Extensão
    </h1>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 text-green-600 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-6">
                    <button id="btn-filtro" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">🔍 Filtrar</button>
                    <a href="{{ route('projetos.index') }}" class="ml-4 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded">Limpar Filtros</a>
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
                            <label class="block mb-1">Cadastrado por:</label>
                            <input type="text" name="cadastrado_por" value="{{ request('cadastrado_por') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Título:</label>
                            <input type="text" name="titulo" value="{{ request('titulo') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Data Início:</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Data Fim:</label>
                            <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Carga Mínima:</label>
                            <input type="number" name="carga_min" value="{{ request('carga_min') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Carga Máxima:</label>
                            <input type="number" name="carga_max" value="{{ request('carga_max') }}" class="w-full border-gray-300 rounded-md py-1">
                        </div>

                        <div>
                            <label class="block mb-1">Status:</label>
                            <select name="status" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="editando" {{ request('status') === 'editando' ? 'selected' : '' }}>Editando</option>
                                <option value="entregue" {{ request('status') === 'entregue' ? 'selected' : '' }}>Entregue</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Aprovação NAPEx:</label>
                            <select name="aprovado_napex" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovado_napex') === 'sim' ? 'selected' : '' }}>Sim</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Aprovação Coordenador:</label>
                            <select name="aprovado_coordenador" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovado_coordenador') === 'sim' ? 'selected' : '' }}>Sim</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1">Aprovação Final:</label>
                            <select name="aprovacao_final" class="w-full border-gray-300 rounded-md py-1">
                                <option value="">-- Todos --</option>
                                <option value="sim" {{ request('aprovacao_final') === 'sim' ? 'selected' : '' }}>Sim</option>
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
                            <tr class="bg-[#251C57] text-white">
                                <th class="py-3 px-6 text-left">Cadastrado por</th>
                                <th class="py-3 px-6 text-left">Título</th>
                                <th class="py-3 px-6 text-left">Data de Início</th>
                                <th class="py-3 px-6 text-left">Data de Fim</th>
                                <th class="py-3 px-6 text-left">Carga Horária</th>
                                <th class="py-3 px-6 text-left">Status</th>
                                <th class="py-3 px-6 text-left">Aprovação NAPEx</th>
                                <th class="py-3 px-6 text-left">Aprovação Coordenador</th>
                                <th class="py-3 px-6 text-left">Aprovação Final</th>

                                <th class="py-3 px-6 text-left">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($projetos as $projeto)
                                <tr class="hover:bg-gray-100">
                                    <td class="py-2 px-6">{{ $projeto->user->name ?? 'Desconhecido' }}</td>
                                    <td class="py-2 px-6">{{ $projeto->titulo }}</td>
                                    <td class="py-2 px-6">{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>
                                    <td class="py-2 px-6">{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>
                                    <td class="py-2 px-6">{{ $projeto->atividades->sum('carga_horaria') ?? 0 }}h</td>
                                    <td class="py-2 px-6">{{ ucfirst($projeto->status) }}</td>
                                    <td class="py-2 px-6">
                                        {{ $projeto->aprovado_napex === 'sim' ? 'Sim' : ($projeto->aprovado_napex === 'nao' ? 'Não' : '--') }}
                                    </td>
                                    <td class="py-2 px-6">
                                        {{ $projeto->aprovado_coordenador === 'sim' ? 'Sim' : ($projeto->aprovado_coordenador === 'nao' ? 'Não' : '--') }}
                                    </td>

                                    <td class="py-2 px-6">
                                        {{ $projeto->aprovado_napex === 'sim' && $projeto->aprovado_coordenador === 'sim' ? 'Sim' : '--' }}
                                    </td>


                                    <td class="py-2 px-6 space-x-2" x-data="{ openModal: false }">
                                        @php
                                            $role = auth()->user()->role;
                                            $isAluno = $role === 'aluno';
                                            $isProfessor = $role === 'professor';
                                            $isNapexOrCoord = in_array($role, ['napex', 'coordenador']);
                                            $podeEditar = $projeto->status === 'editando';
                                            $podeVoltar = $projeto->status === 'entregue' 
                                                && $projeto->aprovado_napex !== 'sim' 
                                                && $projeto->aprovado_coordenador !== 'sim';
                                        @endphp

                                        @if ($isAluno || $isProfessor)
                                            <a href="{{ route('projetos.show', $projeto->id) }}" class="text-blue-600 hover:underline">Visualizar</a>
                                        @endif

                                        @if ($isAluno && $podeEditar)
                                            <a href="{{ route('projetos.edit', $projeto->id) }}" class="text-blue-600 hover:underline">Editar</a>

                                            <!-- Botão que abre o modal -->
                                            <button @click="openModal = true" class="text-red-600 hover:underline">Apagar</button>

                                            <!-- Modal -->
                                            <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                                <div class="bg-white rounded-lg p-6 shadow-lg w-80">
                                                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirmação</h2>
                                                    <p class="mb-6 text-gray-600">Tem certeza que deseja apagar este projeto?</p>
                                                    <div class="flex justify-end space-x-2">
                                                        <button @click="openModal = false"
                                                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-4 rounded">
                                                            Cancelar
                                                        </button>
                                                        <form action="{{ route('projetos.destroy', $projeto->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-4 rounded">
                                                                Apagar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        @elseif ($isProfessor && $podeEditar)
                                            <a href="{{ route('projetos.edit', $projeto->id) }}" class="text-blue-600 hover:underline">Editar</a>
                                        @elseif (($isAluno || $isProfessor) && $podeVoltar)
                                            <form action="{{ route('projetos.voltar', $projeto->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-600 hover:underline">Voltar para Edição</button>
                                            </form>
                                        @elseif ($isNapexOrCoord && $projeto->status === 'entregue')
                                            <a href="{{ route('projetos.show', $projeto->id) }}" class="text-green-700 hover:underline font-semibold">Analise/Parecer</a>
                                        @endif
                                    </td>






                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
