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

    @php
        // --- Lógica para Definir o Estado Atual do Relatório ---
        $resultadoStatus = $resultado->status;
        $napexAprovado = $resultado->aprovado_napex === 'sim';
        $coordAprovado = $resultado->aprovado_coordenador === 'sim';
        $napexReprovado = $resultado->aprovado_napex === 'nao';
        $coordReprovado = $resultado->aprovado_coordenador === 'nao';
        $propostaAprovada = true;
        $relatorioAdicionado = true;
        $emRascunho = $resultadoStatus === 'rascunho';
        $foiEnviado = in_array($resultadoStatus, ['enviado', 'aprovado', 'reprovado']);
        $emAnalise = $resultadoStatus === 'enviado';
        $reprovadoGeral = $resultadoStatus === 'reprovado';
        $aprovadoFinal = $resultadoStatus === 'aprovado';
        function etapaClasseFinal($condicaoPositiva, $isAtual = false, $condicaoNegativa = false) {
            if ($condicaoNegativa) return 'bg-red-500 text-white border-red-600 shadow-md';
            return $condicaoPositiva
                ? 'bg-green-500 text-white border-green-600 shadow-md'
                : ($isAtual ? 'bg-blue-600 text-white border-blue-800 shadow-md animate-pulse' : 'bg-gray-300 text-gray-600 border-gray-400 shadow-sm');
        }
    @endphp

    <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento do Relatório</h3>
    <div class="flex items-center justify-center mb-12">
        {{-- A linha do tempo do status permanece a mesma --}}
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
            {{-- Mensagem de Correções Necessárias --}}
            @if($resultado->status === 'reprovado')
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Correções Necessárias</p>
                    <p>Seu relatório foi devolvido para ajustes. Por favor, verifique os pareceres, faça as correções e reenvie para avaliação.</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Bloco de Identificação do Projeto --}}
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
                                <p class="mt-1 text-gray-600">{{ $professores->pluck('name')->implode(', ') }}</p>
                            </div>
                             <div>
                                <x-input-label value="Alunos Envolvidos" />
                                <p class="mt-1 text-gray-600">{{ $alunos->pluck('name')->implode(', ') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('resultados.update', $resultado) }}" method="POST" enctype="multipart/form-data" id="edit-form">
                        @csrf
                        @method('PUT')

                        {{-- Campos do formulário (atividades, parcerias, etc.) --}}
                        <div class="mt-6">
                            <x-input-label for="atividades_desenvolvidas" value="Atividades Desenvolvidas no Período*" class="font-bold"/>
                            <textarea id="atividades_desenvolvidas" name="atividades_desenvolvidas" rows="8" class="block w-full border-gray-300 rounded-md shadow-sm" required maxlength="15000">{{ old('atividades_desenvolvidas', $resultado->atividades_desenvolvidas) }}</textarea>
                        </div>
                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Parcerias - Organizações Envolvidas (se houver)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="parceiro_organizacao" value="Organização" />
                                    <x-text-input id="parceiro_organizacao" name="parceiro_organizacao" type="text" class="mt-1 block w-full" :value="old('parceiro_organizacao', $resultado->parceiro_organizacao)" maxlength="255"/>
                                </div>
                                <div>
                                    <x-input-label for="parceiro_responsavel" value="Nome do Responsável" />
                                    <x-text-input id="parceiro_responsavel" name="parceiro_responsavel" type="text" class="mt-1 block w-full" :value="old('parceiro_responsavel', $resultado->parceiro_responsavel)" maxlength="255"/>
                                </div>
                                <div>
                                    <x-input-label for="parceiro_endereco" value="Endereço" />
                                    <x-text-input id="parceiro_endereco" name="parceiro_endereco" type="text" class="mt-1 block w-full" :value="old('parceiro_endereco', $resultado->parceiro_endereco)" maxlength="255"/>
                                </div>
                                <div>
                                    <x-input-label for="parceiro_cnpj" value="CNPJ" />
                                    <x-text-input id="parceiro_cnpj" name="parceiro_cnpj" type="text" class="mt-1 block w-full" :value="old('parceiro_cnpj', $resultado->parceiro_cnpj)" maxlength="20"/>
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                     <x-input-label for="parceiro_tipo_participacao" value="Tipo de Participação" />
                                    <x-text-input id="parceiro_tipo_participacao" name="parceiro_tipo_participacao" type="text" class="mt-1 block w-full" :value="old('parceiro_tipo_participacao', $resultado->parceiro_tipo_participacao)" maxlength="255" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <x-input-label for="comunidade_externa" value="Pessoas da Comunidade Externa Envolvidas" class="font-bold"/>
                            <textarea id="comunidade_externa" name="comunidade_externa" rows="4" class="block w-full border-gray-300 rounded-md shadow-sm" maxlength="10000">{{ old('comunidade_externa', $resultado->comunidade_externa) }}</textarea>
                        </div>

                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Gerenciar Anexos Comprobatórios</h3>

                            {{-- Lista de Anexos Atuais para Exclusão --}}
                            @if ($resultado->anexos->isNotEmpty())
                                <div class="mb-6">
                                    <p class="font-semibold text-gray-700 mb-2">Anexos Atuais:</p>
                                    <div class="space-y-3">
                                        @foreach ($resultado->anexos as $anexo)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                                                <div class="flex-1 min-w-0">
                                                    <a href="{{ Storage::url($anexo->path) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:underline truncate" title="{{ $anexo->nome_original }}">
                                                        {{ $anexo->nome_original }}
                                                    </a>
                                                    <p class="text-xs text-gray-500 italic mt-1">"{{ $anexo->descricao }}"</p>
                                                </div>
                                                <div class="flex items-center ml-4">
                                                    <input type="checkbox" name="anexos_a_deletar[]" value="{{ $anexo->id }}" id="delete_anexo_{{ $anexo->id }}" class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                    <label for="delete_anexo_{{ $anexo->id }}" class="ml-2 text-sm font-medium text-red-600">Excluir</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Campos Dinâmicos para Adicionar Novos Anexos --}}
                             <p class="font-semibold text-gray-700 mb-2 pt-4 border-t">Adicionar Novos Anexos (Opcional):</p>
                            <div id="anexos-container" class="space-y-4">
                                {{-- O JavaScript irá adicionar os novos campos de anexo aqui --}}
                            </div>
                            <button type="button" id="add-anexo-button" class="mt-4 text-sm bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                + Adicionar Anexo
                            </button>
                        </div>
                    </form>

                    <div class="flex items-center justify-end mt-6 space-x-4">
                        <button type="submit" form="edit-form" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('anexos-container');
        const addButton = document.getElementById('add-anexo-button');
        let anexoIndex = 0; // Inicia o contador para novos anexos

        function addAnexo() {
            const newIndex = anexoIndex;
            const newAnexo = document.createElement('div');
            newAnexo.classList.add('anexo-item', 'p-4', 'border', 'rounded-md', 'relative');
            newAnexo.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 anexo-label">Novo Anexo ${container.children.length + 1}</label>
                    <button type="button" class="remove-anexo-btn text-red-500 hover:text-red-700 font-bold text-sm">Remover</button>
                </div>
                <div class="space-y-2">
                    <div>
                        <label for="anexo_descricao_${newIndex}" class="block text-xs font-medium text-gray-600">Descrição do Anexo</label>
                        <input type="text" name="anexos[${newIndex}][descricao]" id="anexo_descricao_${newIndex}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" required maxlength="1000">
                    </div>
                    <div>
                        <label for="anexo_arquivo_${newIndex}" class="block text-xs font-medium text-gray-600">Arquivo</label>
                        <input type="file" name="anexos[${newIndex}][arquivo]" id="anexo_arquivo_${newIndex}" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100" required>
                    </div>
                </div>
            `;
            container.appendChild(newAnexo);
            anexoIndex++;
            updateLabels(); // Garante que a numeração dos novos anexos esteja correta
        }

        container.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-anexo-btn')) {
                e.target.closest('.anexo-item').remove();
                updateLabels();
            }
        });

        function updateLabels() {
            const items = container.querySelectorAll('.anexo-item');
            items.forEach((item, index) => {
                item.querySelector('.anexo-label').textContent = `Novo Anexo ${index + 1}`;
            });
        }

        addButton.addEventListener('click', addAnexo);
    });
</script>
</x-app-layout>