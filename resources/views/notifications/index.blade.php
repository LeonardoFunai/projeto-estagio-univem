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
                                    $notificationUrl = null; 
                                    
                                    if (isset($notification->data['url'])) {
                                        $urlPath = $notification->data['url'];

                                        // CORREÇÃO: Substituir o placeholder pelo ID real da notificação
                                        if (str_contains($urlPath, '__NOTIFICATION_ID__')) {
                                            // Se a URL contém o placeholder, ela é uma rota interna (notifications.downloadZip)
                                            $notificationUrl = str_replace('__NOTIFICATION_ID__', $notification->id, $urlPath);
                                            
                                            // Usamos url() apenas para garantir o prefixo de domínio/host
                                            $notificationUrl = url($notificationUrl); 
                                        } else {
                                            // Lógica de fallback para notificações antigas ou genéricas
                                            // ... (mantenha sua lógica de fallback aqui) ...
                                            try {
                                                $notificationUrl = url($urlPath);
                                            } catch (\Exception $e) {
                                                $notificationUrl = route('projetos.index');
                                            }
                                        }
                                    }
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
                                                {{ isset($notification->data['zip_file']) ? 'Baixar Lote ZIP →' : 'Ver detalhes →' }}
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

{{-- Lógica JavaScript para marcar como lida (necessário para download direto) --}}
@push('scripts')
<script>
    function markNotificationAsRead(notificationId) {
        // Envia uma requisição assíncrona para marcar a notificação como lida
        fetch('{{ url('/notifications/read') }}/' + notificationId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                console.log('Notificação marcada como lida:', notificationId);
            } else {
                console.error('Falha ao marcar notificação como lida.');
            }
            // Não impede a navegação para o link de download (o href faz isso)
        })
        .catch(error => {
            console.error('Erro de rede ao marcar notificação como lida:', error);
        });
    }
</script>
@endpush