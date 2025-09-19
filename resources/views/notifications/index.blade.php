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
                                <a href="{{ $notification->data['url'] ?? '#' }}" class="block p-4 rounded-lg transition {{ $notification->read_at ? 'bg-gray-100' : 'bg-blue-50 border border-blue-300' }}">
                                    <p class="font-semibold text-gray-800">{{ $notification->data['titulo_projeto'] ?? 'Notificação' }}</p>
                                    <p class="text-sm text-gray-600">{{ $notification->data['mensagem'] ?? 'Você tem uma nova notificação.' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </a>
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