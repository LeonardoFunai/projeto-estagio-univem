<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalhes da Proposta de Projeto Extensionista - Curricularização da Extensão') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">

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

                <x-slot name="pageTitle">
                    Detalhes do Projeto de Extensão
                </x-slot>

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
                    {{-- Etapas iniciais --}}
                    <div class="flex space-x-10 self-center ">
                        @foreach ([
                            ['label' => 'Proposta Criada', 'cond' => true, 'atual' => false],
                            ['label' => 'Editando', 'cond' => $entregue || $napexAprovado || $coordAprovado || $aprovadoFinal, 'atual' => $status === 'editando'],
                            ['label' => 'Entregue', 'cond' => $napexAprovado || $coordAprovado || $aprovadoFinal, 'atual' => $status === 'entregue'],
                        ] as $i => $etapa)
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($etapa['cond'], $etapa['atual']) }}">
                                    {{ $i + 1 }}
                                </div>
                                <span class="mt-1 text-sm text-center">{{ $etapa['label'] }}</span>
                            </div>

                            @if ($i === 0)
                                {{-- seta entre Proposta Criada -> Editando --}}
                                <div class="w-10 h-1 {{ $status !== 'proposta_criada' ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12 mt-6"></div>
                            @endif
                            @if ($i === 1)
                                {{-- seta entre Editando -> Entregue --}}
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
                            <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($napexAprovado, $status === 'aprovado_napex') }}"> N </div>
                            <span class="mt-1 text-sm text-center">Aprovação NAPEx</span>
                        </div>
                        {{-- Coordenador --}}
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full border-3 flex items-center justify-center {{ etapaClasse($coordAprovado, $status === 'aprovado_coord') }}"> C </div>
                            <span class="mt-1 text-sm text-center">Aprovação Coordenação</span>
                        </div>
                    </div>

                    {{-- seta final --}}
                    <div class="w-10 h-1 self-center {{ $aprovadoFinal ? 'bg-green-500' : 'bg-gray-300' }} shadow-md skew-x-12"></div>

                    {{-- Aprovado Final --}}
                    <div class="flex flex-col self-center items-center">
                        <div class="w-12 h-12 rounded-full border-4 flex items-center justify-center {{ etapaClasse($aprovadoFinal, false) }}"> ✓ </div>
                        <span class="mt-2 text-sm font-semibold text-center {{ $aprovadoFinal ? 'text-black' : 'text-gray-400' }}"> Aprovado </span>
                    </div>
                </div>

                <div class="my-6 flex flex-wrap gap-3">
                    
                    @can('update', $projeto)
                        <a href="{{ route('projetos.edit', ['id' => $projeto->id, 'origem' => 'show']) }}"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                            <img src="{{ asset('img/site/btn-editar.png') }}" alt="Editar" width="20" height="20">
                            Editar Proposta
                        </a>
                    @endcan

                    @can('submit', $projeto)
                        <form action="{{ route('projetos.enviar', $projeto->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                <img src="{{ asset('img/site/btn-enviar.png') }}" alt="Enviar projeto" width="20" height="20">
                                Enviar Projeto
                            </button>
                        </form>
                    @endcan

                    @can('revertToEditing', $projeto)
                         <form action="{{ route('projetos.voltar', $projeto->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                                <img src="{{ asset('img/site/btn-voltar-editar.png') }}" alt="Voltar para edição" width="20" height="20">
                                Voltar para Edição
                            </button>
                        </form>
                    @endcan

                    @can('view', $projeto)
                        <a href="{{ route('projetos.gerarPdf', $projeto->id) }}" class="w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                            Gerar PDF
                        </a>
                    @endcan
                </div>

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
                                <th class="bg-[#251C57] text-white p-4 text-left align-top">Cronograma</th>
                                <td class="bg-white p-4 border-b border-gray-300">
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

                @include('projetos.partials.show-pareceres')

            </div>
        </div>
    </div>
</x-app-layout>