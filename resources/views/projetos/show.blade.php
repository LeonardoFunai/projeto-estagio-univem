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


                <!-- Título + Status -->
                <x-slot name="pageTitle">
                    Detalhes do Projeto de Extensão
                </x-slot>
                <p class="text-center text-gray-600 font-medium ">Status: {{ ucfirst($projeto->status) }}</p>
                
                <!-- Botão de Editar -->
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
                    <div class="mb-4 flex  flex-wrap gap-3">
                        @if ($podeEditar)
                            <a href="{{ route('projetos.edit', $projeto->id) }}"
                            class="bg-yellow-600  hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded w-48 flex items-center gap-2">
                                <img src="{{ asset('img/site/btn-editar.png') }}" alt="Editar" width="20" height="20">
                                Editar Proposta
                            </a>

                        @elseif ($podeVoltar)
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
                    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
                    <div class="mb-10">
                        <p><strong>Número do Projeto:</strong> {{ $projeto->numero_projeto ?? '--' }}</p>
                        <p><strong>Data de Recebimento:</strong> 
                            {{ $projeto->data_recebimento_napex ? \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') : '--' }}
                        </p>
                        <p><strong>Data de Encaminhamento:</strong> 
                            {{ $projeto->data_encaminhamento_parecer ? \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') : '--' }}
                        </p>
                        <p><strong>Aprovação:</strong> {{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</p>
                        <p><strong>Motivo:</strong> {{ $projeto->motivo_napex ?? '--' }}</p>
                    </div>

                    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do Coordenador</h2>
                    <div class="mb-10">
                        <p><strong>Aprovação:</strong> {{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</p>
                        <p><strong>Motivo:</strong> {{ $projeto->motivo_coordenador ?? '--' }}</p>
                        <p><strong>Data do Parecer:</strong> 
                            {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                        </p>
                    </div>
                @endif


                <!-- FORMULÁRIO DO NAPEX -->
                @if(auth()->user()->role === 'napex')
                <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do Coordenador</h2>
                <div class="mb-10">
                    <p><strong>Aprovação:</strong> {{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</p>
                    <p><strong>Motivo:</strong> {{ $projeto->motivo_coordenador ?? '--' }}</p>
                    <p><strong>Data do Parecer:</strong> 
                        {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                    </p>
                </div>  


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
                        <div class="mb-10">
                            <p><strong>Número do Projeto:</strong> {{ $projeto->numero_projeto }}</p>
                            <p><strong>Data de Recebimento:</strong> {{ \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') }}</p>
                            <p><strong>Data de Encaminhamento:</strong> {{ \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') }}</p>
                            <p><strong>Aprovação:</strong> {{ ucfirst($projeto->aprovado_napex) }}</p>
                            <p><strong>Motivo:</strong> {{ $projeto->motivo_napex }}</p>
                            <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'napex']) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded mt-4 inline-block">Editar Parecer</a>
                        </div>
                    @endif
                @endif

                <!-- FORMULÁRIO DO COORDENADOR -->
                @if(auth()->user()->role === 'coordenador')
                <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
                <div class="mb-10">
                    <p><strong>Número do Projeto:</strong> {{ $projeto->numero_projeto ?? '--' }}</p>
                    <p><strong>Data de Recebimento:</strong> 
                        {{ $projeto->data_recebimento_napex ? \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') : '--' }}
                    </p>
                    <p><strong>Data de Encaminhamento:</strong> 
                        {{ $projeto->data_encaminhamento_parecer ? \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') : '--' }}
                    </p>
                    <p><strong>Aprovação:</strong> {{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</p>
                    <p><strong>Motivo:</strong> {{ $projeto->motivo_napex ?? '--' }}</p>
                </div>


                    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do Coordenador de Curso</h2>
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
                        <div class="mb-10">
                            <p><strong>Aprovação:</strong> {{ ucfirst($projeto->aprovado_coordenador) }}</p>
                            <p><strong>Motivo:</strong> {{ $projeto->motivo_coordenador }}</p>
                            <p><strong>Data do Parecer:</strong> {{ \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') }}</p>
                            <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'coordenador']) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded mt-4 inline-block">Editar Parecer</a>
                        </div>
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
