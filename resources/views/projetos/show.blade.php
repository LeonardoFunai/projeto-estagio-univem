<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalhes da Proposta de Projeto Extensionista - Curricularização da Extensão') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">

                <!-- Título + Status -->
                <h1 class="text-2xl font-bold text-[#251C57] text-center mb-2">Detalhes do Projeto</h1>
                <p class="text-center text-gray-600 font-medium mb-8">Status: {{ ucfirst($projeto->status) }}</p>

                <!-- TABELA 1 - Detalhes do Projeto -->
                <div class="overflow-x-auto">
                    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                        <tbody>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left w-1/5">Título</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->titulo }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Período</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->periodo }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Professor(es) envolvidos</th>
                                <td class="bg-white p-4 border-b border-gray-300">
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
                                <td class="bg-white p-4 border-b border-gray-300">
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
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->publico_alvo }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Período da realização do projeto</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    {{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}
                                </td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Introdução</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->introducao }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Objetivos do Projeto</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->objetivo_geral }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Justificativa do Projeto</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->justificativa }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Metodologia</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->metodologia }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Atividades a serem desenvolvidas</th>
                                <td class="bg-white p-4 border-b border-gray-300">
                                    @if ($projeto->atividades && $projeto->atividades->count())
                                        <ul class="list-disc pl-5">
                                            @foreach ($projeto->atividades as $atividade)
                                                <li class="mb-2">
                                                    <p><strong>O que fazer:</strong> {{ $atividade->o_que_fazer }}</p>
                                                    <p><strong>Como fazer:</strong> {{ $atividade->como_fazer }}</p>
                                                    <p><strong>Carga Horária:</strong> {{ $atividade->carga_horaria }} horas</p>
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
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->resultados_esperados }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Arquivo</th>
                                <td class="bg-white p-4">
                                    @if ($projeto->arquivo)
                                        <a href="{{ route('projetos.download', $projeto->id) }}" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                                            📄 Ver/Download do Arquivo
                                        </a>
                                    @else
                                        Nenhum arquivo enviado.
                                    @endif
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

                <!-- Parecer do NAPEx -->
                <h1 class="text-2xl font-bold text-[#251C57] text-center mb-8">Parecer do Núcleo de Apoio à Pesquisa e Extensão</h1>

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
                        <tbody>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left w-1/5">Número do Projeto</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento NAPEx</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ \Carbon\Carbon::parse($projeto->data_recebimento_napex)->format('d/m/Y') }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento para Pareceres</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ \Carbon\Carbon::parse($projeto->data_encaminhamento_parecer)->format('d/m/Y') }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Aprovação NAPEx</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Motivo NAPEx</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex }}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Parecer do Coordenador -->
                <h1 class="text-2xl font-bold text-[#251C57] text-center mb-8">Parecer do Coordenador de Curso</h1>

                <div class="overflow-x-auto">
                    <table class="min-w-full w-full border border-gray-300 rounded-lg">
                        <tbody>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left w-1/5">Aprovação Coordenador</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Motivo Coordenador</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador }}</td>
                            </tr>

                            <tr>
                                <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer Coordenador</th>
                                <td class="bg-white p-4 border-b border-gray-300">{{ \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') }}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
