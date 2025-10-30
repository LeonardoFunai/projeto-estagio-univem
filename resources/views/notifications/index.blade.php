{{-- Em resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <x-slot name="pageTitle">
        Minhas Notificações
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">Minhas Notificações</h2>

                    @if($notifications->isEmpty())
                        <p class="text-gray-500">Você não tem nenhuma notificação.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                @php
                                    // ==========================================================
                                    // INÍCIO DA CORREÇÃO
                                    // ==========================================================
                                    $notificationUrl = null; // Começa como nulo
                                    
                                    if (isset($notification->data['url'])) {
                                        $urlPath = $notification->data['url'];

                                        // Verificação de segurança:
                                        // Se a URL for malformada (ex: '/projetos/' ou '/resultados/' sem ID), 
                                        // redireciona para a página de índice de projetos como um fallback seguro.
                                        if ($urlPath === '/projetos/' || $urlPath === '/resultados/' || str_ends_with($urlPath, '/projetos/') || str_ends_with($urlPath, '/resultados/')) {
                                            $notificationUrl = route('projetos.index');
                                        } else {
                                            try {
                                                // Tenta gerar a URL absoluta
                                                $notificationUrl = url($urlPath);
                                            } catch (\Exception $e) {
                                                // Se falhar (rota inválida, etc), aponta para o índice
                                                $notificationUrl = route('projetos.index');
                                            }
                                        }
                                    }
                                    // ==========================================================
                                    // FIM DA CORREÇÃO
                                    // ==========================================================
                                @endphp

                                {{-- Bloco principal da notificação --}}
                                <div class="p-4 rounded-lg flex items-start gap-4 transition {{ $notification->read_at ? 'bg-gray-100' : 'bg-blue-50 border border-blue-200' }}">
                                    
                                    {{-- Ícone de Informação --}}
                                    <div class="text-blue-500 text-xl pt-1">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </div>

                                    {{-- Conteúdo da Notificação --}}
                                    <div class="flex-grow">
                                        {{-- Linha do Título e Horário --}}
                                        <div class="flex justify-between items-baseline">
                                            <h3 class="font-semibold text-gray-800">
                                                {{-- Usa a chave 'titulo' que definimos nas novas notificações, ou a antiga 'titulo_projeto' como fallback --}}
                                                {{ $notification->data['titulo'] ?? ($notification->data['titulo_projeto'] ?? 'Notificação') }}
                                            </h3>
                                            <p class="text-xs text-gray-500 whitespace-nowrap ml-4">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                        {{-- Mensagem --}}
                                        <p class="text-sm text-gray-600 mt-1">{{ $notification->data['mensagem'] ?? 'Você tem uma nova notificação.' }}</p>

                                        {{-- Link (se houver e for válido) --}}
                                        @if($notificationUrl)
                                            <a href="{{ $notificationUrl }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">
                                                Ver detalhes →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>