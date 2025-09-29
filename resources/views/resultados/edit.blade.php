@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str; 
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Relatório de Mensuração de Resultados
        </h2>
    </x-slot>

<x-slot name="pageTitle">
    Edição do Relatório de Resultados
</x-slot>

    <!-- Trilha de Status -->

    @php
        // --- Lógica para Definir o Estado Atual do Relatório ---
        $resultadoStatus = $resultado->status;

        // Condições de Aprovação e Reprovação
        $napexAprovado = $resultado->aprovado_napex === 'sim';
        $coordAprovado = $resultado->aprovado_coordenador === 'sim';
        $napexReprovado = $resultado->aprovado_napex === 'nao';
        $coordReprovado = $resultado->aprovado_coordenador === 'nao';

        // --- Status Gerais do Fluxo ---
        $propostaAprovada = true; // Etapa 1: Sempre concluída
        $relatorioAdicionado = true; // Etapa 2: Sempre concluída, pois o resultado existe para ser visto
        
        $emRascunho = $resultadoStatus === 'rascunho'; // Etapa 3: É o estado ATUAL?
        $foiEnviado = in_array($resultadoStatus, ['enviado', 'aprovado', 'reprovado']); // Já passou da etapa de rascunho?
        
        $emAnalise = $resultadoStatus === 'enviado'; // Etapa 4: É o estado ATUAL?
        
        $reprovadoGeral = $resultadoStatus === 'reprovado';
        $aprovadoFinal = $resultadoStatus === 'aprovado';

        // --- Função Helper de Estilo ---
        function etapaClasseFinal($condicaoPositiva, $isAtual = false, $condicaoNegativa = false) {
            if ($condicaoNegativa) return 'bg-red-500 text-white border-red-600 shadow-md';
            return $condicaoPositiva
                ? 'bg-green-500 text-white border-green-600 shadow-md'
                : ($isAtual ? 'bg-blue-600 text-white border-blue-800 shadow-md animate-pulse' : 'bg-gray-300 text-gray-600 border-gray-400 shadow-sm');
        }
    @endphp

    <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento do Relatório</h3>

    <div class="flex items-center justify-center">

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($propostaAprovada) }}">
                <span>✓</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Proposta<br>Aprovada</span>
        </div>

        <div class="w-12 border-t-4 {{ etapaClasseFinal($propostaAprovada) }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($relatorioAdicionado) }}">
                <span>1</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Adicionado</span>
        </div>

        <div class="w-12 border-t-4 {{ $foiEnviado ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($foiEnviado, $emRascunho) }}">
                <span>2</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Rascunho</span>
        </div>

        <div class="w-12 border-t-4 {{ $foiEnviado ? 'border-green-500' : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($aprovadoFinal || $reprovadoGeral, $emAnalise) }}">
                <span>3</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Entregue</span>
        </div>

        <div class="w-12 border-t-4 {{ ($napexAprovado || $coordAprovado || $reprovadoGeral) ? ($reprovadoGeral ? 'border-red-500' : 'border-green-500') : 'border-gray-300' }} mx-1"></div>

        <div class="flex flex-col space-y-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($napexAprovado, false, $napexReprovado) }}">
                    <span class="text-xs font-bold">N</span>
                </div>
                <span class="ml-2 text-sm">Parecer NAPEX</span>
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($coordAprovado, false, $coordReprovado) }}">
                    <span class="text-xs font-bold">C</span>
                </div>
                <span class="ml-2 text-sm">Parecer Coord.</span>
            </div>
        </div>

        <div class="w-12 border-t-4 {{ $aprovadoFinal ? 'border-green-500' : ($reprovadoGeral ? 'border-red-500' : 'border-gray-300') }} mx-1"></div>

        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center {{ etapaClasseFinal($aprovadoFinal, false, $reprovadoGeral) }}">
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


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($resultado->status === 'reprovado')
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Correções Necessárias</p>
                    <p>Seu relatório foi devolvido para ajustes. Por favor, verifique os pareceres abaixo, faça as correções e reenvie para avaliação.</p>
                    
                    @if($resultado->parecer_napex)
                        <div class="mt-4">
                            <strong class="text-sm">Parecer do NAPEX:</strong>
                            <p class="text-sm italic whitespace-pre-wrap">{{ $resultado->parecer_napex }}</p>
                        </div>
                    @endif

                    @if($resultado->parecer_coordenador)
                        <div class="mt-4">
                            <strong class="text-sm">Parecer da Coordenação:</strong>
                            <p class="text-sm italic whitespace-pre-wrap">{{ $resultado->parecer_coordenador }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-8 p-4 border border-gray-200 rounded-md">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">IDENTIFICAÇÃO DO PROJETO</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Título do Projeto" />
                                <p class="mt-1 text-gray-600">{{ $resultado->projeto->titulo }}</p>
                            </div>
                            <div>
                                <x-input-label value="Período" />
                                <p class="mt-1 text-gray-600">{{ $resultado->projeto->periodo }}</p>
                            </div>
                            <div>
                                <x-input-label value="Professor(es) Envolvidos" />
                                {{-- Usa a variável injetada ($professores) e o campo 'name' --}}
                                <p class="mt-1 text-gray-600">{{ $professores->pluck('name')->implode(', ') }}</p>
                            </div>
                             <div>
                                <x-input-label value="Alunos Envolvidos" />
                                {{-- Usa a variável injetada ($alunos) e o campo 'name' --}}
                                <p class="mt-1 text-gray-600">{{ $alunos->pluck('name')->implode(', ') }}</p>
                            </div>
                        </div>
                    </div>




                    <form action="{{ route('resultados.update', $resultado) }}" method="POST" enctype="multipart/form-data" id="edit-form">
                        @csrf
                        @method('PUT')

                         <div class="mt-6">
                            <x-input-label for="atividades_desenvolvidas" value="Atividades Desenvolvidas no Período*" class="font-bold"/>
                            <p class="text-sm text-gray-500 mb-2">Descreva o que foi desenvolvido, em qual instituição, quantas pessoas foram envolvidas, se atingiu os resultados esperados e sugestões de melhoria.</p>
                            <textarea id="atividades_desenvolvidas" name="atividades_desenvolvidas" rows="8" class="block w-full border-gray-300 rounded-md shadow-sm" required>{{ old('atividades_desenvolvidas', $resultado->atividades_desenvolvidas) }}</textarea>
                            <x-input-error :messages="$errors->get('atividades_desenvolvidas')" class="mt-2" />
                        </div>

                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Parcerias - Organizações Envolvidas (se houver)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="parceiro_organizacao" value="Organização" />
                                    <x-text-input id="parceiro_organizacao" name="parceiro_organizacao" type="text" class="mt-1 block w-full" :value="old('parceiro_organizacao', $resultado->parceiro_organizacao)" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_responsavel" value="Nome do Responsável" />
                                    <x-text-input id="parceiro_responsavel" name="parceiro_responsavel" type="text" class="mt-1 block w-full" :value="old('parceiro_responsavel', $resultado->parceiro_responsavel)" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_endereco" value="Endereço" />
                                    <x-text-input id="parceiro_endereco" name="parceiro_endereco" type="text" class="mt-1 block w-full" :value="old('parceiro_endereco', $resultado->parceiro_endereco)" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_cnpj" value="CNPJ" />
                                    <x-text-input id="parceiro_cnpj" name="parceiro_cnpj" type="text" class="mt-1 block w-full" :value="old('parceiro_cnpj', $resultado->parceiro_cnpj)" />
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                     <x-input-label for="parceiro_tipo_participacao" value="Tipo de Participação" />
                                    <x-text-input id="parceiro_tipo_participacao" name="parceiro_tipo_participacao" type="text" class="mt-1 block w-full" :value="old('parceiro_tipo_participacao', $resultado->parceiro_tipo_participacao)" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-input-label for="comunidade_externa" value="Pessoas da Comunidade Externa Envolvidas" class="font-bold"/>
                            <p class="text-sm text-gray-500 mb-2">Liste os nomes das pessoas ou organizações da comunidade externa que participaram.</p>
                            <textarea id="comunidade_externa" name="comunidade_externa" rows="4" class="block w-full border-gray-300 rounded-md shadow-sm">{{ old('comunidade_externa', $resultado->comunidade_externa) }}</textarea>
                        </div>


                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Gerenciar Anexos</h3>

                            {{-- 1. LISTA DE ANEXOS ATUAIS COM OPÇÃO DE DELETAR --}}
                            @if ($resultado->anexos->isNotEmpty())
                                <div class="mb-6">
                                    <p class="font-semibold text-sm text-gray-800 mb-2">Anexos Atuais:</p>
                                    <div class="space-y-2">
                                        @foreach ($resultado->anexos as $anexo)
                                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                <a href="{{ Storage::url($anexo->path) }}" target="_blank" class="text-blue-600 hover:underline truncate" title="{{ $anexo->nome_original }}">
                                                    {{ Str::limit($anexo->nome_original, 40) }}
                                                </a>
                                                {{-- Checkbox para marcar para deleção --}}
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="anexos_a_deletar[]" value="{{ $anexo->id }}" id="delete_anexo_{{ $anexo->id }}" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                                    <label for="delete_anexo_{{ $anexo->id }}" class="ml-2 text-sm text-red-600">Excluir</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- 2. CAMPO PARA DESCRIÇÃO DOS ANEXOS (JÁ EXISTENTE E CORRETO) --}}
                            <div>
                                <x-input-label for="anexos_descricao" value="Descrição dos Anexos" />
                                <p class="text-sm text-gray-500 mb-2">Você pode atualizar a descrição ou deixar como está.</p>
                                <x-text-input id="anexos_descricao" name="anexos_descricao" type="text" class="mt-1 block w-full" :value="old('anexos_descricao', $resultado->anexos_descricao)" />
                            </div>

                            {{-- 3. CAMPO PARA ADICIONAR NOVOS ANEXOS (UNIFICADO) --}}
                            <div class="mt-4">
                                <x-input-label for="anexos" value="Adicionar Novos Arquivos (Opcional)" />
                                <p class="text-sm text-gray-500 mb-2">Selecione novos arquivos para incluir no relatório. Os arquivos atuais serão mantidos, a menos que marcados para "Excluir".</p>
                                <input id="anexos" name="anexos[]" type="file" multiple class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 mt-1"/>
                                <x-input-error :messages="$errors->get('anexos.*')" class="mt-2" />
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <button type="submit" form="edit-form" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Salvar Rascunho
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>