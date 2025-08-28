<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Relatório de Mensuração de Resultados
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Correções Necessárias</p>
                <p>Seu relatório foi devolvido para ajustes. Por favor, verifique os pareceres abaixo, faça as correções necessárias no formulário e reenvie para avaliação.</p>
                
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


            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('resultados.update', $resultado) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Importante: Define o método como PUT para a rota de update --}}

                        <div class="mt-6">
                            <label for="atividades_desenvolvidas" class="block font-medium text-sm text-gray-700">Atividades Desenvolvidas no Período*</label>
                            <textarea id="atividades_desenvolvidas" name="atividades_desenvolvidas" rows="8" class="block w-full border-gray-300 rounded-md shadow-sm" required>{{ old('atividades_desenvolvidas', $resultado->atividades_desenvolvidas) }}</textarea>
                        </div>

                        {{-- ... (outros campos do formulário preenchidos) ... --}}
                        {{-- Vou adicionar apenas mais um como exemplo, a lógica é a mesma --}}

                        <div class="mt-8 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Parcerias</h3>
                            <div>
                                <label for="parceiro_organizacao" class="block font-medium text-sm text-gray-700">Organização</label>
                                <input id="parceiro_organizacao" name="parceiro_organizacao" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('parceiro_organizacao', $resultado->parceiro_organizacao) }}">
                            </div>
                            {{-- Repita a lógica acima para os outros campos de parceria --}}
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Salvar Alterações e Reenviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>