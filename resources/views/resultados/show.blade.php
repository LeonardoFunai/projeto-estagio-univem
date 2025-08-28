<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Visualização do Relatório de Resultados
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">IDENTIFICAÇÃO DO PROJETO</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Título:</strong>
                        <p class="text-gray-900">{{ $resultado->projeto->titulo }}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Período:</strong>
                        <p class="text-gray-900">{{ $resultado->projeto->periodo }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">RELATÓRIO DE RESULTADOS</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Atividades Desenvolvidas no Período:</strong>
                        <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $resultado->atividades_desenvolvidas }}</p>
                    </div>
                    <hr>
                    <div>
                        <strong class="text-gray-600">Pessoas da Comunidade Externa Envolvidas:</strong>
                        <p class="mt-1 text-gray-900">{{ $resultado->comunidade_externa ?? 'N/A' }}</p>
                    </div>
                    <hr>
                    <div>
                        <strong class="text-gray-600">Parcerias:</strong>
                        @if($resultado->parceiro_organizacao)
                            <p class="mt-1 text-gray-900">
                                {{ $resultado->parceiro_organizacao }} (Resp: {{ $resultado->parceiro_responsavel ?? 'N/A' }})
                            </p>
                        @else
                            <p class="mt-1 text-gray-900">Nenhuma parceria envolvida.</p>
                        @endif
                    </div>
                     <hr>
                    <div>
                        <strong class="text-gray-600">Anexos (Fotos e Vídeos):</strong>
                        <p class="mt-1 text-gray-900">{{ $resultado->anexos_descricao ?? 'Nenhuma descrição fornecida.' }}</p>
                        {{-- Aqui você pode adicionar a lógica para mostrar os links das fotos e vídeos --}}
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">PARECERES DA AVALIAÇÃO</h3>

                {{-- Lógica para mostrar os formulários de parecer --}}
                {{-- Vamos implementar a lógica de salvar esses pareceres no próximo passo --}}

                <div class="space-y-6">
                    {{-- Parecer NAPEX --}}
                    <div>
                        <h4 class="font-semibold">Parecer do NAPEX</h4>
                        @if($resultado->parecer_napex)
                            <p class="mt-1 text-sm text-gray-700 border p-3 rounded-md bg-gray-50 whitespace-pre-wrap">{{ $resultado->parecer_napex }}</p>
                            <p class="mt-2 text-sm"><strong>Status:</strong> {{ ucfirst($resultado->aprovado_napex) }}</p>
                        @else
                            {{-- AQUI ENTRARIA O FORMULÁRIO PARA O NAPEX PREENCHER --}}
                            <p class="text-sm text-gray-500">Aguardando avaliação do NAPEX.</p>
                        @endif
                    </div>

                    {{-- Parecer Coordenação --}}
                    <div>
                        <h4 class="font-semibold">Parecer da Coordenação</h4>
                        @if($resultado->parecer_coordenador)
                            <p class="mt-1 text-sm text-gray-700 border p-3 rounded-md bg-gray-50 whitespace-pre-wrap">{{ $resultado->parecer_coordenador }}</p>
                            <p class="mt-2 text-sm"><strong>Status:</strong> {{ ucfirst($resultado->aprovado_coordenador) }}</p>
                        @else
                            {{-- AQUI ENTRARIA O FORMULÁRIO PARA A COORDENAÇÃO PREENCHER --}}
                            <p class="text-sm text-gray-500">Aguardando avaliação da Coordenação.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>