<x-app-layout>
    <x-slot name="pageTitle">
        <h2 class="font-semibold text-xl text-blue-800 leading-tight">
            {{ __('Importar Alunos em Lote') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Alertas de erro --}}
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erro!</strong>
                    <span class="block sm:inline">{!! session('error') !!}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 bg-white border-b border-gray-200 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium">Instruções</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Para importar os alunos, envie um arquivo Excel (.xlsx ou .xls) com as seguintes colunas obrigatórias na primeira linha:
                            </p>
                            <ul class="mt-2 list-disc list-inside text-sm text-gray-600 bg-gray-50 p-3 rounded-md">
                                <li><strong>nome</strong> (Nome completo do aluno)</li>
                                <li><strong>email</strong> (Email único para cada aluno)</li>
                                <li><strong>ra</strong> (R.A. único para cada aluno)</li>
                                <li><strong>senha</strong> (Senha com no mínimo 8 caracteres)</li>
                                <li><strong>curso</strong> (Nome exato do curso como está no sistema, ex: "Ciência da Computação")</li>
                                <li><strong>cpf</strong> (Opcional)</li>
                                <li><strong>data_nascimento</strong> (Opcional, no formato AAAA-MM-DD)</li>
                            </ul>
                        </div>

                        <hr>

                        <div>
                            <x-input-label for="file" :value="__('Arquivo Excel')" />
                            <input type="file" name="file" id="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Importar Alunos') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>