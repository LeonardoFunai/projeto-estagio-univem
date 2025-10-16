<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório de Mensuração de Resultados
        </h2>
    </x-slot>

    {{-- Linha do Tempo (Status do Relatório) --}}
    <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento do Relatório</h3>
    <div class="flex items-center justify-center mb-12">
        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-green-500 text-white border-green-600 shadow-md">
                <span>✓</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Proposta<br>Aprovada</span>
        </div>
        <div class="w-16 border-t-4 border-green-500 mx-1"></div>
        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-blue-600 text-white border-blue-800 shadow-md animate-pulse">
                <span>1</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Adicionado</span>
        </div>
        <div class="w-16 border-t-4 border-gray-300 mx-1"></div>
        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm">
                <span>2</span>
            </div>
            <span class="mt-2 text-sm font-semibold">Relatório<br>Entregue</span>
        </div>
        <div class="w-16 border-t-4 border-gray-300 mx-1"></div>
        <div class="flex flex-col space-y-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm">
                    <span class="text-xs font-bold">N</span>
                </div>
                <span class="ml-2 text-sm text-gray-500">Parecer NAPEX</span>
            </div>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm">
                    <span class="text-xs font-bold">C</span>
                </div>
                <span class="ml-2 text-sm text-gray-500">Parecer Coord.</span>
            </div>
        </div>
        <div class="w-16 border-t-4 border-gray-300 mx-1"></div>
        <div class="flex flex-col items-center text-center w-24">
            <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm">
                <span class="text-2xl"></span>
            </div>
            <span class="mt-2 text-sm font-semibold text-gray-500">Aprovado</span>
        </div>
    </div>

    {{-- Conteúdo do Formulário --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Bloco de Identificação do Projeto --}}
                    <div class="mb-8 p-4 border border-gray-200 rounded-md">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">IDENTIFICAÇÃO DO PROJETO</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Título do Projeto" />
                                <p class="mt-1 text-gray-600">{{ $projeto->titulo }}</p>
                            </div>
                            <div>
                                <x-input-label value="Período" />
                                <p class="mt-1 text-gray-600">{{ $projeto->periodo }}</p>
                            </div>
                            <div>
                                <x-input-label value="Período de Realização" />
                                <p class="mt-1 text-gray-600">{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <x-input-label value="Carga Horária Total" />
                                <p class="mt-1 text-gray-600">{{ $cargaHorariaTotal }} horas</p>
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

                    <form action="{{ route('resultados.store', $projeto->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Atividades Desenvolvidas --}}
                        <div class="mt-6">
                            <x-input-label for="atividades_desenvolvidas" value="Atividades Desenvolvidas no Período*" class="font-bold"/>
                            <p class="text-sm text-gray-500 mb-2">Descreva o que foi desenvolvido, em qual instituição, quantas pessoas foram envolvidas, se atingiu os resultados esperados e sugestões de melhoria.</p>
                            <textarea maxlength="15000" id="atividades_desenvolvidas" name="atividades_desenvolvidas" rows="15" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('atividades_desenvolvidas') }}</textarea>
                            <x-input-error :messages="$errors->get('atividades_desenvolvidas')" class="mt-2" />
                        </div>

                        {{-- Parcerias --}}
                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Parcerias - Organizações Envolvidas (se houver)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="parceiro_organizacao" value="Organização" />
                                    <x-text-input id="parceiro_organizacao" name="parceiro_organizacao" type="text" class="mt-1 block w-full" :value="old('parceiro_organizacao')" maxlength="255" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_responsavel" value="Nome do Responsável" />
                                    <x-text-input id="parceiro_responsavel" name="parceiro_responsavel" type="text" class="mt-1 block w-full" :value="old('parceiro_responsavel')" maxlength="255"/>
                                </div>
                                <div>
                                    <x-input-label for="parceiro_endereco" value="Endereço" />
                                    <x-text-input id="parceiro_endereco" name="parceiro_endereco" type="text" class="mt-1 block w-full" :value="old('parceiro_endereco')" maxlength="255" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_cnpj" value="CNPJ" />
                                    <x-text-input id="parceiro_cnpj" name="parceiro_cnpj" type="text" class="mt-1 block w-full" :value="old('parceiro_cnpj')" maxlength="20" />
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                     <x-input-label for="parceiro_tipo_participacao" value="Tipo de Participação" />
                                    <x-text-input id="parceiro_tipo_participacao" name="parceiro_tipo_participacao" type="text" class="mt-1 block w-full" :value="old('parceiro_tipo_participacao')" maxlength="255"/>
                                </div>
                            </div>
                        </div>

                        {{-- Comunidade Externa --}}
                        <div class="mt-6">
                            <x-input-label for="comunidade_externa" value="Pessoas da Comunidade Externa Envolvidas" class="font-bold" />
                            <p class="text-sm text-gray-500 mb-2">Liste os nomes das pessoas ou organizações da comunidade externa que participaram.</p>
                            <textarea maxlength="5000" id="comunidade_externa" name="comunidade_externa" rows="4" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('comunidade_externa') }}</textarea>
                        </div>

                        {{-- Seção de Anexos Dinâmicos --}}
                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                             <h3 class="text-lg font-bold text-gray-800 mb-4">Anexos Comprobatórios</h3>
                             <div id="anexos-container" class="space-y-4">
                                 </div>
                             <button type="button" id="add-anexo-btn" class="mt-4 text-sm bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                 + Adicionar Anexo
                             </button>
                        </div>

                        {{-- Botão de Salvar --}}
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Salvar Relatório de Resultados') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('anexos-container');
        const addButton = document.getElementById('add-anexo-btn');
        let anexoIndex = 0;

        function addAnexo() {
            const newIndex = anexoIndex; // Captura o índice atual
            const newAnexo = document.createElement('div');
            newAnexo.classList.add('anexo-item', 'p-4', 'border', 'rounded-md', 'relative');
            newAnexo.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 anexo-label">Anexo ${container.children.length + 1}</label>
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
                item.querySelector('.anexo-label').textContent = `Anexo ${index + 1}`;
            });
        }

        // Adiciona o primeiro anexo por padrão
        addAnexo();

        addButton.addEventListener('click', addAnexo);
    });
</script>