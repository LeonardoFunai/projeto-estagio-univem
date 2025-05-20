    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes da Proposta de Projeto Extensionista - Curricularização da Extensão') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="w-full px-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">



                    <!-- mensagens de erro e sucesso -->
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong>Erro:</strong> {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            <strong>Sucesso:</strong> {{ session('success') }}
                        </div>
                    @endif


                    <!-- Título  -->
                    <x-slot name="pageTitle">
                        Detalhes do Projeto de Extensão
                    </x-slot>

                    <!--Trilha do Status -->

                    @php
                        $status = $projeto->status;

                        $napexAprovado = $projeto->aprovado_napex === 'sim';
                        $coordAprovado = $projeto->aprovado_coordenador === 'sim';
                        $entregue = $status === 'entregue';
                        $aprovadoFinal = $napexAprovado && $coordAprovado;


                        function etapaClasse($condicao, $atual = false) {
                            return $condicao
                                ? 'bg-green-500 text-white border-green-600 shadow-md'
                                : ($atual ? 'bg-blue-600 text-white border-blue-800 shadow-md animate-pulse' : 'bg-gray-300 text-gray-600 border-gray-400 shadow-sm');
                        }
                    @endphp

                    <div class="flex items-end justify-center space-x-10 mt-5">

                        {{-- Etapas iniciais — deslocadas para cima --}}
                        <div class="flex space-x-10 self-center ">
                            @foreach ([
                                ['label' => 'Proposta Criada', 'cond' => true, 'atual' => false],
                                ['label' => 'Editando',
                                'cond' => $entregue || $napexAprovado || $coordAprovado || $aprovadoFinal, // já passou dessa etapa
                                'atual' => $status === 'editando'],
                                [    'label' => 'Entregue',
                                'cond' => $napexAprovado || $coordAprovado || $aprovadoFinal, // se já foi aprovado por algum
                                'atual' => $status === 'entregue'],
                            ] as $i => $etapa)
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($etapa['cond'], $etapa['atual']) }}">
                                        {{ $i + 1 }}
                                    </div>
                                    <span class="mt-1 text-sm  text-center">{{ $etapa['label'] }}</span>
                                </div>

                                @if ($i === 0)
                                    {{-- seta entre Proposta Criada -> Editando (sempre verde ou posterior) --}}
                                    <div class="w-10 h-1 {{ $status !== 'proposta_criada' ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12 mt-6"></div>
                                @endif

                                @if ($i === 1)
                                    {{-- seta entre Editando -> Entregue (só verde se realmente entregou ou passou disso) --}}
                                    <div class="w-10 h-1 {{ in_array($status, ['entregue', 'aprovado_napex', 'aprovado_coord', 'aprovado']) ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12 mt-6"></div>
                                @endif

                            @endforeach
                        </div>


                        {{-- seta para aprovações --}}
                        <div class="w-10 h-1 {{ ($napexAprovado || $coordAprovado) ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12 self-center"></div>

                        {{-- APROVAÇÕES EMPILHADAS --}}
                        <div class="flex flex-col justify-between space-y-6 items-center mt-[-32px]">
                            {{-- Napex --}}
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($napexAprovado, $status === 'aprovado_napex') }}">
                                    N
                                </div>
                                <span class="mt-1 text-sm  text-center">Aprovação NAPEx</span>
                            </div>

                            {{-- Coordenador --}}
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($coordAprovado, $status === 'aprovado_coord') }}">
                                    C
                                </div>
                                <span class="mt-1 text-sm  text-center">Aprovação Coordenação</span>
                            </div>
                        </div>

                        {{-- seta final --}}
                    
                            <div class="w-10 h-1 self-center {{ $aprovadoFinal ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12"></div>


                            {{-- Aprovado Final --}}
                            <div class="flex flex-col self-center items-center">
                                <div class="w-12 h-12 rounded-full border-4 flex items-center justify-center {{ etapaClasse($aprovadoFinal, false) }}">
                                    ✓
                                </div>
                                <span class="mt-2 text-sm font-semibold text-center {{ $aprovadoFinal ? 'text-black' : 'text-gray-400' }}">
                                    Aprovado
                                </span>
                            </div>
                    
                    </div>

                    @php
                        $role = auth()->user()->role;
                        $isAluno = $role === 'aluno';
                        $isProfessor = $role === 'professor';
                        $podeEditar = $projeto->status === 'editando';
                        $podeVoltar = $projeto->status === 'entregue' 
                            && $projeto->aprovado_napex === 'pendente' 
                            && $projeto->aprovado_coordenador === 'pendente';
                    @endphp

                    @if ($isAluno || $isProfessor)
                        <div class="mb-4 flex flex-wrap gap-3">
                            @if ($podeEditar)
                                {{-- Botão Editar --}}
                                <a href="{{ route('projetos.edit', ['id' => $projeto->id, 'origem' => 'show']) }}"
                                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                    <img src="{{ asset('img/site/btn-editar.png') }}" alt="Editar" width="20" height="20">
                                    Editar Proposta
                                </a>


                                {{-- Botão Enviar (somente aluno) --}}
                                @if ($isAluno)
                                    <form action="{{ route('projetos.enviar', $projeto->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                            <img src="{{ asset('img/site/btn-enviar.png') }}" alt="Enviar projeto" width="20" height="20">
                                            Enviar Projeto
                                        </button>
                                    </form>
                                @endif
                            @elseif ($podeVoltar)
                                {{-- Botão Voltar para edição --}}
                                <form action="{{ route('projetos.voltar', $projeto->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                        <img src="{{ asset('img/site/btn-voltar-editar.png') }}" alt="Voltar para edição" width="20" height="20">
                                        Voltar para Edição
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif





                    <!-- TABELA 1 - Detalhes do Projeto -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                            
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/5">Título</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->titulo }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Período</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->periodo }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Professor(es) envolvidos</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        @if ($projeto->professores && $projeto->professores->count())
                                            <ul class="list-disc pl-5">
                                                @foreach ($projeto->professores as $prof)
                                                    <li><strong>{{ $prof->nome }}</strong>
                                                        @if($prof->email) – Email: {{ $prof->email }} @endif
                                                        @if($prof->area) – Área: {{ $prof->area }} @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Nenhum professor registrado.
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Alunos envolvidos</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        @if ($projeto->alunos && $projeto->alunos->count())
                                            <ul class="list-disc pl-5">
                                                @foreach ($projeto->alunos as $aluno)
                                                    <li><strong>{{ $aluno->nome }}</strong> — RA: {{ $aluno->ra }} — Curso: {{ $aluno->curso }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Nenhum aluno registrado.
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Público Alvo da Atividade</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->publico_alvo }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Período da realização do projeto</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Introdução</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->introducao }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Justificativa do Projeto</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->justificativa }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Metodologia</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->metodologia }}
                                    </td>
                                </tr>

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Atividades a serem desenvolvidas</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        @if ($projeto->atividades && $projeto->atividades->count())
                                            <ul class="list-disc pl-5">
                                                @foreach ($projeto->atividades as $atividade)
                                                    <li class="list-decimal">
                                                        <p style="max-width: 100%; word-wrap: break-word; white-space: pre-line;">
                                                            <strong>O que fazer:</strong> {{ $atividade->o_que_fazer }}
                                                        </p>
                                                        <p style="max-width: 100%; word-wrap: break-word; white-space: pre-line;">
                                                            <strong>Como fazer:</strong> {{ $atividade->como_fazer }}
                                                        </p>
                                                        <p style="max-width: 100%; word-wrap: break-word;">
                                                            <strong>Carga Horária:</strong> {{ $atividade->carga_horaria }} horas
                                                        </p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Nenhuma atividade registrada.
                                        @endif
                                    </td>
                                </tr>


                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Cronograma</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        @if ($projeto->cronogramas && $projeto->cronogramas->count())
                                            <table class="table-auto w-full">
                                                <thead>
                                                    <tr>
                                                        <th class="text-left py-2 px-3 border-b">Atividade</th>
                                                        <th class="text-left py-2 px-3 border-b">Mês</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($projeto->cronogramas as $cronograma)
                                                        <tr>
                                                            <td class="py-2 px-3 border-b">{{ $cronograma->atividade }}</td>
                                                            <td class="py-2 px-3 border-b">{{ $cronograma->mes }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            Nenhum cronograma registrado.
                                        @endif
                                    </td>
                                </tr>


                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Resultados Esperados</th>
                                    <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                        {{ $projeto->resultados_esperados }}
                                    </td>
                                </tr>

                                

                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Criado em</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Última edição</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->updated_at->format('d/m/Y H:i:s') }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- TABELA DE PARECERES - VISUAL PARA ALUNO -->
                    @if(auth()->user()->role === 'aluno')
                        <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do NAPEx</h2>
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-6">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Número do Projeto</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_recebimento_napex ? \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_encaminhamento_parecer ? \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>


                    @endif


                    <!-- FORMULÁRIO/VISÃO DO NAPEX -->
                    @if(auth()->user()->role === 'napex')
                        
                        <!-- Tabela do coordenador que aparece para o napex -->
                        <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do Coordenador</h2>
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Form do Napex -->
                        <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
                        @if(request('editar') === 'napex' || is_null($projeto->aprovado_napex))
                            <form method="POST" action="{{ route('projetos.avaliar.napex', $projeto->id) }}" class="mb-10">
                                @csrf
                                <label>Número do Projeto</label>
                                <input type="text" name="numero_projeto" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('numero_projeto', $projeto->numero_projeto) }}">

                                <label>Data de Recebimento</label>
                                <input type="date" name="data_recebimento_napex" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_recebimento_napex', $projeto->data_recebimento_napex) }}">

                                <label>Data de Encaminhamento</label>
                                <input type="date" name="data_encaminhamento_parecer" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_encaminhamento_parecer', $projeto->data_encaminhamento_parecer) }}">

                                <label>Aprovação</label>
                                <select name="aprovado_napex" class="w-full border-gray-300 rounded-md mb-4">
                                    <option value="">Selecione</option>
                                    <option value="sim" {{ $projeto->aprovado_napex == 'sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="nao" {{ $projeto->aprovado_napex == 'nao' ? 'selected' : '' }}>Não</option>
                                </select>

                                <label>Motivo</label>
                                <textarea name="motivo_napex" class="w-full border-gray-300 rounded-md mb-4">{{ old('motivo_napex', $projeto->motivo_napex) }}</textarea>

                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Enviar Parecer</button>
                            </form>
                        @else

                        <!-- tabela de napex que aparece p/ napex-->
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-4">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Número do Projeto</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_recebimento_napex ? \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_encaminhamento_parecer ? \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'napex']) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-6">
                            Editar Parecer
                        </a>
                        @endif
                    @endif

                    <!-- FORMULÁRIO/VISAO DO COORDENADOR -->
                    @if(auth()->user()->role === 'coordenador')

                        <!-- Tabela do napex que aparece para coordenador -->
                        <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-6">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Número do Projeto</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_recebimento_napex ? \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_encaminhamento_parecer ? \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Form do coordenador -->
                        @if(request('editar') === 'coordenador' || is_null($projeto->aprovado_coordenador))
                            <form method="POST" action="{{ route('projetos.avaliar.coordenador', $projeto->id) }}" class="mb-10">
                                @csrf
                                <label>Aprovação</label>
                                <select name="aprovado_coordenador" class="w-full border-gray-300 rounded-md mb-4">
                                    <option value="">Selecione</option>
                                    <option value="sim" {{ $projeto->aprovado_coordenador == 'sim' ? 'selected' : '' }}>Sim</option>
                                    <option value="nao" {{ $projeto->aprovado_coordenador == 'nao' ? 'selected' : '' }}>Não</option>
                                </select>

                                <label>Motivo</label>
                                <textarea name="motivo_coordenador" class="w-full border-gray-300 rounded-md mb-4">{{ old('motivo_coordenador', $projeto->motivo_coordenador) }}</textarea>

                                <label>Data do Parecer</label>
                                <input type="date" name="data_parecer_coordenador" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_parecer_coordenador', $projeto->data_parecer_coordenador) }}">

                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Enviar Parecer</button>
                            </form>
                        @else

                        <!-- Tabela coordenador que aparece para coordenador -->
                        <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
                        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-4">
                            <tbody>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Aprovação</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                                    <td class="bg-white p-4 border-b border-gray-300">
                                        {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'coordenador']) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-10">
                            Editar Parecer
                        </a>

                        @endif
                    @endif


                    <!-- Rejeições -->
                    @if ($projeto->rejeicoes->count() > 0)
                        <h1 class="text-2xl font-bold text-[#251C57] text-center mb-8">Rejeições Registradas</h1>
                        <div class="overflow-x-auto">
                            <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                            <thead>
                                <tr>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Data da Rejeição</th>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/2">Motivo</th>
                                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Responsável</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projeto->rejeicoes as $rejeicao)
                                    <tr>
                                        <td class="bg-white p-4 border-b border-gray-300">
                                            {{ \Carbon\Carbon::parse($rejeicao->data_rejeicao)->format('d/m/Y') }}
                                        </td>
                                        <td class="bg-white p-4 border-b border-gray-300">
                                            {{ $rejeicao->motivo }}
                                        </td>
                                        <td class="bg-white p-4 border-b border-gray-300">
                                            {{ $rejeicao->autor === 'napex' ? 'NAPEx' : ($rejeicao->autor === 'coordenador' ? 'Coordenação' : 'Desconhecido') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </x-app-layout>
