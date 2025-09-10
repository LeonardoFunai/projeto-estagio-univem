<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório de Mensuração de Resultados
        </h2>
    </x-slot>


    <h3 class="text-lg font-bold text-gray-800 mb-6 text-center">Andamento do Relatório</h3>

    <div class="flex items-center justify-center">

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



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

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
                                <x-input-label value="Professor(es) Envolvidos" />
                                <p class="mt-1 text-gray-600">{{ $projeto->professores->pluck('nome')->implode(', ') }}</p>
                            </div>
                             <div>
                                <x-input-label value="Alunos Envolvidos" />
                                <p class="mt-1 text-gray-600">{{ $projeto->alunos->pluck('nome')->implode(', ') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('resultados.store', $projeto->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mt-6">
                            <x-input-label for="atividades_desenvolvidas" value="Atividades Desenvolvidas no Período*" class="font-bold"/>
                            <p class="text-sm text-gray-500 mb-2">Descreva o que foi desenvolvido, em qual instituição, quantas pessoas foram envolvidas, se atingiu os resultados esperados e sugestões de melhoria.</p>
                            <textarea id="atividades_desenvolvidas" name="atividades_desenvolvidas" rows="8" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('atividades_desenvolvidas') }}</textarea>
                            <x-input-error :messages="$errors->get('atividades_desenvolvidas')" class="mt-2" />
                        </div>

                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Parcerias - Organizações Envolvidas (se houver)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="parceiro_organizacao" value="Organização" />
                                    <x-text-input id="parceiro_organizacao" name="parceiro_organizacao" type="text" class="mt-1 block w-full" :value="old('parceiro_organizacao')" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_responsavel" value="Nome do Responsável" />
                                    <x-text-input id="parceiro_responsavel" name="parceiro_responsavel" type="text" class="mt-1 block w-full" :value="old('parceiro_responsavel')" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_endereco" value="Endereço" />
                                    <x-text-input id="parceiro_endereco" name="parceiro_endereco" type="text" class="mt-1 block w-full" :value="old('parceiro_endereco')" />
                                </div>
                                <div>
                                    <x-input-label for="parceiro_cnpj" value="CNPJ" />
                                    <x-text-input id="parceiro_cnpj" name="parceiro_cnpj" type="text" class="mt-1 block w-full" :value="old('parceiro_cnpj')" />
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                     <x-input-label for="parceiro_tipo_participacao" value="Tipo de Participação" />
                                    <x-text-input id="parceiro_tipo_participacao" name="parceiro_tipo_participacao" type="text" class="mt-1 block w-full" :value="old('parceiro_tipo_participacao')" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-input-label for="comunidade_externa" value="Pessoas da Comunidade Externa Envolvidas" class="font-bold"/>
                            <p class="text-sm text-gray-500 mb-2">Liste os nomes das pessoas ou organizações da comunidade externa que participaram.</p>
                            <textarea id="comunidade_externa" name="comunidade_externa" rows="4" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('comunidade_externa') }}</textarea>
                        </div>

                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                             <h3 class="text-lg font-bold text-gray-800 mb-4">Anexos</h3>
                             <div>
                                <x-input-label for="anexos_descricao" value="Descrição dos Anexos" />
                                <p class="text-sm text-gray-500 mb-2">Descreva brevemente os arquivos que você está enviando (Ex: Fotos do evento, vídeo de apresentação, etc.).</p>
                                <x-text-input id="anexos_descricao" name="anexos_descricao" type="text" class="mt-1 block w-full" :value="old('anexos_descricao')" />
                             </div>
    
                            <div class="mt-4">
                                <x-input-label for="anexos" value="Upload de Arquivos (Múltiplos arquivos permitidos)" />
                                <p class="text-sm text-gray-500 mb-2">Envie os arquivos comprobatórios do seu relatório (Ex: Fotos, vídeos, PDFs, etc.). Você pode selecionar múltiplos arquivos de uma vez.</p>
                                
                                <input id="anexos" name="anexos[]" type="file" multiple class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 mt-1"/>
                                
                                <x-input-error :messages="$errors->get('anexos.*')" class="mt-2" />
                            </div>
                            
                        </div>

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