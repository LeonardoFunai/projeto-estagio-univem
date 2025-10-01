<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <x-input-label for="name" :value="__('Nome Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Perfil do Usuário')" />
                            
                            @if(auth()->user()->role === 'admin' && auth()->id() !== $user->id)
                                <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="aluno" @if(old('role', $user->role) == 'aluno') selected @endif>Aluno</option>
                                    <option value="professor" @if(old('role', $user->role) == 'professor') selected @endif>Professor</option>
                                    <option value="napex" @if(old('role', $user->role) == 'napex') selected @endif>NAPEX</option>
                                    <option value="coordenador" @if(old('role', $user->role) == 'coordenador') selected @endif>Coordenador</option>
                                    <option value="admin" @if(old('role', $user->role) == 'admin') selected @endif>Admin</option>
                                </select>
                            @else
                                <x-text-input id="role-display" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="ucfirst($user->role)" disabled />
                                <input type="hidden" name="role" value="{{ $user->role }}">
                            @endif
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div id="dados-aluno" class="hidden mt-4 space-y-4 p-4 border rounded-md bg-gray-50">
                             <p class="font-medium text-sm text-gray-700">Informações Adicionais do Aluno</p>
                            
                             <div>
                                <x-input-label for="cpf" :value="__('CPF')" />
                                <x-text-input id="cpf" class="block mt-1 w-full" type="text" name="cpf" :value="old('cpf', $user->cpf)" />
                                <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ra" :value="__('R.A.')" />
                                <x-text-input id="ra" class="block mt-1 w-full" type="text" name="ra" :value="old('ra', $user->ra)" />
                                <x-input-error :messages="$errors->get('ra')" class="mt-2" />
                            </div>

                             <div>
                                <x-input-label for="data_nascimento" :value="__('Data de Nascimento')" />
                                <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento', $user->data_nascimento ? \Carbon\Carbon::parse($user->data_nascimento)->format('Y-m-d') : '')" />
                                <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="curso_id" :value="__('Curso')" />
                                <select id="curso_id" name="curso_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Selecione o curso --</option>
                                    @foreach ($cursos as $curso)
                                        <option value="{{ $curso->id }}" @if(old('curso_id', $user->curso_id) == $curso->id) selected @endif>{{ $curso->nome }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('curso_id')" class="mt-2" />
                            </div>
                        </div>
                        
                        @if(auth()->user()->role === 'admin')
                            <div id="dados-coordenador" class="hidden mt-4 space-y-4 p-4 border rounded-md bg-gray-50">
                                 <p class="font-medium text-sm text-gray-700">Cursos Coordenados</p>
                                 <select id="cursos_coordenados" name="cursos_coordenados[]" multiple class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                     @foreach ($cursos as $curso)
                                         <option value="{{ $curso->id }}" @if($user->cursosCoordenados->contains($curso->id)) selected @endif>{{ $curso->nome }}</option>
                                     @endforeach
                                 </select>
                            </div>
                        @endif

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Nova Senha (deixe em branco para manter a atual)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nova Senha')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Atualizar Usuário') }}
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
                const currentRole = roleSelect ? roleSelect.value : document.querySelector('input[name="role"]').value;

                dadosAlunoDiv.classList.toggle('hidden', currentRole !== 'aluno');
                
                if (dadosCoordenadorDiv) {
                    dadosCoordenadorDiv.classList.toggle('hidden', currentRole !== 'coordenador');
                }
            }
            
            toggleConditionalFields();

            if (roleSelect) {
                roleSelect.addEventListener('change', toggleConditionalFields);
            }
        });
    </script>
</x-app-layout>