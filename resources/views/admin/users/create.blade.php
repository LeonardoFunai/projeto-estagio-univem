<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adicionar Novo Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        {{-- Informações Básicas --}}
                        <div>
                            <x-input-label for="name" :value="__('Nome Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        @if(auth()->user()->role === 'admin')
                            <div class="mt-4">
                                <x-input-label for="role" :value="__('Perfil do Usuário')" />
                                <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Selecione um perfil --</option>
                                    <option value="aluno" @if(old('role') == 'aluno') selected @endif>Aluno</option>
                                    <option value="professor" @if(old('role') == 'professor') selected @endif>Professor</option>
                                    <option value="napex" @if(old('role') == 'napex') selected @endif>NAPEX</option>
                                    <option value="coordenador" @if(old('role') == 'coordenador') selected @endif>Coordenador</option>
                                    <option value="admin" @if(old('role') == 'admin') selected @endif>Admin</option>
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>
                        @endif

                        <div id="dados-aluno" class="{{ auth()->user()->role === 'admin' ? 'hidden' : '' }} mt-4 space-y-4 p-4 border rounded-md bg-gray-50">
                            <p class="font-medium text-sm text-gray-700">Informações Adicionais do Aluno</p>
                            
                            <div>
                                <x-input-label for="cpf" :value="__('CPF')" />
                                <x-text-input id="cpf" class="block mt-1 w-full" type="text" name="cpf" :value="old('cpf')" />
                                <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="ra" :value="__('R.A.')" />
                                <x-text-input id="ra" class="block mt-1 w-full" type="text" name="ra" :value="old('ra')" />
                                <x-input-error :messages="$errors->get('ra')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="data_nascimento" :value="__('Data de Nascimento')" />
                                <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento')" />
                                <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="curso_id" :value="__('Curso')" />
                                <select id="curso_id" name="curso_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Selecione o curso --</option>
                                    @foreach ($cursos as $curso)
                                        <option value="{{ $curso->id }}" @if(old('curso_id') == $curso->id) selected @endif>{{ $curso->nome }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('curso_id')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div id="dados-coordenador" class="hidden mt-4 space-y-4 p-4 border rounded-md bg-gray-50">
                             <p class="font-medium text-sm text-gray-700">Cursos Coordenados</p>
                             <select id="cursos_coordenados" name="cursos_coordenados[]" multiple class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                 @foreach ($cursos as $curso)
                                     <option value="{{ $curso->id }}">{{ $curso->nome }}</option>
                                 @endforeach
                             </select>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Senha')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Criar Usuário') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const dadosAlunoDiv = document.getElementById('dados-aluno');
            const dadosCoordenadorDiv = document.getElementById('dados-coordenador');

            function toggleConditionalFields() {
                if (!roleSelect) return;

                dadosAlunoDiv.classList.toggle('hidden', roleSelect.value !== 'aluno');
                dadosCoordenadorDiv.classList.toggle('hidden', roleSelect.value !== 'coordenador');
            }

            if(roleSelect) {
                toggleConditionalFields();
                roleSelect.addEventListener('change', toggleConditionalFields);
            }
        });
    </script>
</x-app-layout>