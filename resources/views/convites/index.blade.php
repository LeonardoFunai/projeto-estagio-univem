<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meus Convites') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Convites Pendentes</h3>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @forelse ($convites as $convite)
                        <div class="mb-4 p-4 border rounded-md flex justify-between items-center">
                            <div>
                                <p class="text-gray-800">
                                    Você foi convidado por <span class="font-semibold">{{ $convite->inviter->name }}</span> para participar do projeto:
                                </p>
                                <p class="text-lg font-semibold text-blue-700">{{ $convite->projeto->titulo }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('convites.aceitar', $convite) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Aceitar</button>
                                </form>
                                <form action="{{ route('convites.recusar', $convite) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Recusar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">Você não tem convites pendentes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>