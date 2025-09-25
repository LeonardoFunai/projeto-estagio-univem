<x-app-layout>
    <x-slot name="pageTitle">
        <h2 class="font-semibold text-xl text-blue-800 leading-tight">
            {{ __('Gerenciamento de Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            {{-- Alertas --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Adicionar Usuário
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-lg font-bold">Lista de Usuários</h3>
                        
                        {{-- FORMULÁRIO DE FILTRO ATUALIZADO --}}
                        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row flex-wrap items-center gap-2 w-full md:w-auto">
                            <input type="text" name="search" placeholder="Buscar por nome, email, CPF ou RA..." class="border-gray-300 rounded-md shadow-sm" value="{{ $search ?? '' }}">
                            
                            <select name="role" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos os Perfis</option>
                                @foreach ($roles as $roleOption)
                                    <option value="{{ $roleOption }}" @if(isset($role) && $role == $roleOption) selected @endif>{{ ucfirst(str_replace('_', ' ', $roleOption)) }}</option>
                                @endforeach
                            </select>

                            {{-- NOVO FILTRO DE CURSO --}}
                            <select name="curso_id" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">Todos os Cursos</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" @if(isset($curso_id) && $curso_id == $curso->id) selected @endif>{{ $curso->nome }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded">Filtrar</button>
                            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded text-center">Limpar</a>
                        </form>
                        
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perfil (Role)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->curso->nome ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum usuário encontrado com os filtros aplicados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>