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
                        
                        {{-- Informações Básicas --}}
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

                        {{-- Perfil (Role) --}}
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Perfil do Usuário')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="aluno" @if(old('role', $user->role) == 'aluno') selected @endif>Aluno</option>
                                <option value="professor" @if(old('role', $user->role) == 'professor') selected @endif>Professor</option>
                                <option value="admin" @if(old('role', $user->role) == 'admin') selected @endif>Admin</option>
                                <option value="napex" @if(old('role', $user->role) == 'napex') selected @endif>NAPEX</option>
                                <optgroup label="Coordenadores">
                                    <option value="coordenador_adm" @if(old('role', $user->role) == 'coordenador_adm') selected @endif>Coordenador de Administração</option>
                                    <option value="coordenador_cc" @if(old('role', $user->role) == 'coordenador_cc') selected @endif>Coordenador de Ciência da Computação</option>
                                    <option value="coordenador_contabeis" @if(old('role', $user->role) == 'coordenador_contabeis') selected @endif>Coordenador de Ciências Contábeis</option>
                                    <option value="coordenador_design" @if(old('role', $user->role) == 'coordenador_design') selected @endif>Coordenador de Design Gráfico</option>
                                    <option value="coordenador_direito" @if(old('role', $user->role) == 'coordenador_direito') selected @endif>Coordenador de Direito</option>
                                    <option value="coordenador_producao" @if(old('role', $user->role) == 'coordenador_producao') selected @endif>Coordenador de Engenharia de Produção</option>
                                    <option value="coordenador_si" @if(old('role', $user->role) == 'coordenador_si') selected @endif>Coordenador de Sistemas de Informação</option>
                                </optgroup>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        {{-- Campos Condicionais para Aluno --}}
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
                                <x-text-input id="data_nascimento" class="block mt-1 w-full" type="date" name="data_nascimento" :value="old('data_nascimento', $user->data_nascimento ? $user->data_nascimento->format('Y-m-d') : '')" />
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

                        {{-- Senha --}}
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

            function toggleDadosAluno() {
                if (roleSelect.value === 'aluno') {
                    dadosAlunoDiv.classList.remove('hidden');
                } else {
                    dadosAlunoDiv.classList.add('hidden');
                }
            }
            
            toggleDadosAluno();
            roleSelect.addEventListener('change', toggleDadosAluno);
        });
    </script>
</x-app-layout>