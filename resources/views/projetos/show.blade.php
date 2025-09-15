<x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes da Proposta de Projeto Extensionista - Curricularização da Extensão') }}
            </h2>
        </x-slot>
        
        @php
            // --- Lógica para Definir o Estado Atual do Projeto ---
            $projetoStatus = $projeto->status;

            // Condições de Aprovação e Reprovação
            $napexAprovado = $projeto->aprovado_napex === 'sim';
            $coordAprovado = $projeto->aprovado_coordenador === 'sim';
            $napexReprovado = $projeto->aprovado_napex === 'nao';
            $coordReprovado = $projeto->aprovado_coordenador === 'nao';

            // Status Gerais do Fluxo
            $propostaCriada = true; // Etapa 1: Sempre concluída
            $emEdicao = $projetoStatus === 'editando'; // Etapa 2: É o estado atual?
            $foiEnviado = in_array($projetoStatus, ['entregue', 'aprovado', 'reprovado']); // Etapa 3: Já passou daqui?
            $emAnalise = $projetoStatus === 'entregue'; // Etapa 3: É o estado atual?
            $reprovadoGeral = $projetoStatus === 'reprovado';
            $aprovadoFinal = $projetoStatus === 'aprovado';

            // --- Função Helper de Estilo ---
            function etapaClasseProjetoFinal($condicaoPositiva, $isAtual = false, $condicaoNegativa = false) {
                if ($condicaoNegativa) return 'bg-red-500 text-white border-red-600 shadow-md';
                return $condicaoPositiva
                    ? 'bg-green-500 text-white border-green-600 shadow-md'
                    : ($isAtual ? 'bg-blue-600 text-white border-blue-800 shadow-md animate-pulse' : 'bg-gray-300 text-gray-600 border-gray-400 shadow-sm');
            }

            $totalHoras = $projeto->atividades->sum('carga_horaria');
        @endphp

        
        <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento da Proposta</h3>

        <div class="flex items-center justify-center">

            <div class="flex flex-col items-center text-center w-20">
                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasseProjetoFinal($propostaCriada) }}">
                    <span>1</span>
                </div>
                <span class="mt-2 text-sm font-semibold">Proposta<br>Criada</span>
            </div>

            <div class="w-24 border-t-4 {{ $emEdicao || $foiEnviado ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

            <div class="flex flex-col items-center text-center w-20">
                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasseProjetoFinal($foiEnviado, $emEdicao, $reprovadoGeral) }}">
                    <span>2</span>
                </div>
                <span class="mt-2 text-sm font-semibold">Editando</span>
            </div>

            <div class="w-24 border-t-4 {{ $foiEnviado ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

            <div class="flex flex-col items-center text-center w-20">
                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasseProjetoFinal($aprovadoFinal, $emAnalise, $reprovadoGeral) }}">
                    <span>3</span>
                </div>
                <span class="mt-2 text-sm font-semibold">Entregue</span>
            </div>
            
            <div class="w-24 border-t-4 {{ ($napexAprovado || $coordAprovado || $reprovadoGeral) ? ($reprovadoGeral ? 'border-red-500' : 'border-green-500') : 'border-gray-300' }} mx-1"></div>

            <div class="flex flex-col space-y-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center {{ etapaClasseProjetoFinal($napexAprovado, false, $napexReprovado) }}">
                        <span class="text-xs font-bold">N</span>
                    </div>
                    <span class="ml-2 text-sm">Parecer NAPEX</span>
                </div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center {{ etapaClasseProjetoFinal($coordAprovado, false, $coordReprovado) }}">
                        <span class="text-xs font-bold">C</span>
                    </div>
                    <span class="ml-2 text-sm">Parecer Coord.</span>
                </div>
            </div>

            <div class="w-24 border-t-4 {{ $aprovadoFinal ? 'border-green-500' : ($reprovadoGeral ? 'border-red-500' : 'border-gray-300') }} mx-1"></div>

            <div class="flex flex-col items-center text-center w-20">
                <div class="w-11 h-11 rounded-full border-3 flex items-center justify-center {{ etapaClasseProjetoFinal($aprovadoFinal, false, $reprovadoGeral) }}">
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
        



        <div class="py-3">
            <div class="w-full">
                
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
                    Detalhes da Proposta de Extensão
                </x-slot>

                

                @php
                    $role = auth()->user()->role;
                    $isAluno = $role === 'aluno';
                    $isProfessor = $role === 'professor';
                    $podeEditar = $projeto->status === 'editando';
                    $podeVoltar = $projeto->status === 'entregue' 
                        && $projeto->aprovado_napex === 'pendente' 
                        && $projeto->aprovado_coordenador === 'pendente';
                @endphp

               

                <div class="mb-4 flex flex-wrap gap-2">
                    {{-- Botão Gerar PDF agora está no mesmo contêiner --}}
                    <a href="{{ route('projetos.gerarPdf', $projeto->id) }}" class="w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded flex items-center gap-2">
                        Gerar PDF
                    </a>
                    @if ($isAluno || $isProfessor)
                        @if ($podeEditar)
                            {{-- Botão Editar --}}
                            <a href="{{ route('projetos.edit', ['id' => $projeto->id, 'origem' => 'show']) }}"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4  rounded flex items-center gap-2">
                                <img src="{{ asset('img/site/btn-editar.png') }}" alt="Editar" width="16" height="15">
                                Editar Proposta
                            </a>

                            {{-- Botão Enviar (somente aluno) --}}
                            @if ($isAluno)
                                <form action="{{ route('projetos.enviar', $projeto->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                        <img src="{{ asset('img/site/btn-enviar.png') }}" alt="Enviar projeto" width="18" height="20">
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
                    @endif

                   @if (($projeto->etapa === 'Resultado' || $projeto->etapa === 'Concluído') && $projeto->resultado)
                        <a href="{{ route('resultados.show', $projeto->resultado) }}" title="Visualizar Relatório"  class="w-auto bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-3 rounded flex items-center gap-2" >Ver Resultado</a>
                    @endif
                    

                </div>
                
                
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
                                                <li><strong>{{ $aluno->nome }}</strong> — RA: {{ $aluno->ra }} — Curso: {{ $aluno->curso->nome }}</li>
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
                                <th class="bg-[#251C57] text-white p-4 text-left">Objetivo do Projeto</th>
                                <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                    {{ $projeto->objetivo_geral }}
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
                                <th class="bg-[#251C57] text-white p-4 text-left align-top">Atividades e Carga Horária</th>
                                <td class="bg-white p-2 border-b border-gray-300">
                                    @if ($projeto->atividades && $projeto->atividades->count())
                                        <table class="table-auto w-full text-sm">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="text-left py-2 px-3 border-b-2 border-gray-300 font-semibold text-gray-700">O que fazer</th>
                                                    <th class="text-left py-2 px-3 border-b-2 border-gray-300 font-semibold text-gray-700">Como fazer</th>
                                                    <th class="text-center py-2 px-3 border-b-2 border-gray-300 font-semibold text-gray-700" style="width: 120px;">Carga Horária</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($projeto->atividades as $atividade)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-2 px-3 border-b border-gray-200" style="white-space: pre-line;">{{ $atividade->o_que_fazer }}</td>
                                                        <td class="py-2 px-3 border-b border-gray-200" style="white-space: pre-line;">{{ $atividade->como_fazer }}</td>
                                                        <td class="py-2 px-3 border-b border-gray-200 text-center">{{ $atividade->carga_horaria }} horas</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-right font-bold py-2 px-3 border-t-2 border-gray-300">Total de Horas da Proposta:</td>
                                                    <td class="text-center font-bold py-2 px-3 border-t-2 border-gray-300">{{ $totalHoras }} horas</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @else
                                        <p class="text-gray-500">Nenhuma atividade registrada.</p>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Total de Horas da Proposta</th>
                                <td class="bg-white p-4 border-b border-gray-300 font-bold text-lg">
                                    {{ $totalHoras }} horas
                                </td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left align-top">Cronograma</th>
                                <td class="bg-white p-2  border-b border-gray-300">
                                    @if ($projeto->cronogramas && $projeto->cronogramas->count() > 0)
                                        <table class="table-auto w-full text-sm">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="text-left py-2 px-3 border-b-2 border-gray-300 font-semibold text-gray-700">Atividade</th>
                                                    <th class="text-left py-2 px-3 border-b-2 border-gray-300 font-semibold text-gray-700">Período</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($projeto->cronogramas as $itemCronograma)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-2 px-3 border-b border-gray-200">{{ $itemCronograma->atividade }}</td>
                                                        <td class="py-2 px-3 border-b border-gray-200">
                                                            @if (!empty($itemCronograma->mes_inicio) && !empty($itemCronograma->mes_fim))
                                                                @if ($itemCronograma->mes_inicio == $itemCronograma->mes_fim)
                                                                    {{ $itemCronograma->mes_inicio }}
                                                                @else
                                                                    {{ $itemCronograma->mes_inicio }} a {{ $itemCronograma->mes_fim }}
                                                                @endif
                                                            @elseif (!empty($itemCronograma->mes_inicio))
                                                                {{-- Caso apenas o mês de início esteja definido --}}
                                                                {{ $itemCronograma->mes_inicio }}
                                                            @else
                                                                <span class="text-gray-500">Período não definido</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-gray-500">Nenhum cronograma registrado.</p>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Recursos Necessários</th>
                                <td class="bg-white p-4 border-b border-gray-300" style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                                    {{ $projeto->recursos }}
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
                                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
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
                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
                    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                        <tbody>
                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                                </td>
                            </tr>
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
                        <form id="form-parecer-napex" method="POST" action="{{ route('projetos.avaliar.napex', $projeto->id) }}" class="mb-10">
                            @csrf
                            <label>Número do Projeto</label>
                            <input type="text" name="numero_projeto" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('numero_projeto', $projeto->numero_projeto) }}">


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
                                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
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
                    
                        <!-- Botão editar  -->
                        @if ($projeto->status != 'aprovado')
                            <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'napex']) }}#form-parecer-napex"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-6">
                                Editar Parecer
                            </a>
                        @endif
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
                                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
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
                        <form id="form-parecer-coordenador" method="POST" action="{{ route('projetos.avaliar.coordenador', $projeto->id) }}" class="mb-10">
                            @csrf
                            <label>Aprovação</label>
                            <select name="aprovado_coordenador" class="w-full border-gray-300 rounded-md mb-4">
                                <option value="">Selecione</option>
                                <option value="sim" {{ $projeto->aprovado_coordenador == 'sim' ? 'selected' : '' }}>Sim</option>
                                <option value="nao" {{ $projeto->aprovado_coordenador == 'nao' ? 'selected' : '' }}>Não</option>
                            </select>

                            <label>Motivo</label>
                            <textarea name="motivo_coordenador" class="w-full border-gray-300 rounded-md mb-4">{{ old('motivo_coordenador', $projeto->motivo_coordenador) }}</textarea>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Enviar Parecer</button>
                        </form>
                    @else

                    <!-- Tabela coordenador que aparece para coordenador -->
                    <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
                    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-4">
                        <tbody>
                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                                </td>
                            </tr>
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

                        <!-- Botão de Editar -->
                    @if($projeto->status != 'aprovado')
                        <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'coordenador']) }}#form-parecer-coordenador"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-10">
                            Editar Parecer
                        </a>
                    @endif

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

                <div class="mt-10">
                    <h2 class="text-xl font-bold text-[#251C57] mb-4">Histórico Detalhado</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full border border-gray-300 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Data</th>
                                    <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Usuário</th>
                                    <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Origem</th>
                                    <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Ação</th>
                                    <th class="text-left py-2 px-3 border-b-2 font-semibold text-gray-700">Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Verifica se a variável $projeto existe e se a coleção de logs não está vazia --}}
                                @if ($projeto && $projeto->todosOsLogs->isNotEmpty())
                                    @foreach ($projeto->todosOsLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 border-b">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td class="py-2 px-3 border-b">{{ $log->user->name ?? 'Sistema' }}</td>
                                            <td class="py-2 px-3 border-b">
                                                @if (str_contains($log->loggable_type, 'Projeto'))
                                                    <span class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">
                                                        Proposta
                                                    </span>
                                                @elseif (str_contains($log->loggable_type, 'Resultado'))
                                                    <span class="px-2 py-1 font-semibold leading-tight text-purple-700 bg-purple-100 rounded-full">
                                                        Relatório
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="py-2 px-3 border-b">{{ $log->acao }}</td>
                                            <td class="py-2 px-3 border-b">{{ $log->descricao }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Caso não haja logs, exibe uma mensagem na tabela --}}
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500">Nenhum histórico de alterações encontrado.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </x-app-layout>
