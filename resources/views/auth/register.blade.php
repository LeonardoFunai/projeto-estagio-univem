@extends('layouts.auth')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nome -->
        <div class="input-wrapper">
            <span class="auth-icon">🧑</span>
            <label for="name" class="auth-label">Nome</label>
            <input id="name" type="text" name="name" placeholder="Nome"
                   value="{{ old('name') }}" required autofocus class="auth-input">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-white" />
        </div>

        <!-- Email -->
        <div class="input-wrapper">
            <span class="auth-icon">📧</span>
            <label for="email" class="auth-label">Email</label>
            <input id="email" type="email" name="email" placeholder="Email"
                   value="{{ old('email') }}" required class="auth-input">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-white" />
        </div>

        <!-- Senha -->
        <div class="input-wrapper">
            <span class="auth-icon">🔒</span>
            <label for="password" class="auth-label">Senha</label>
            <input id="password" type="password" name="password" placeholder="Senha"
                   required class="auth-input">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-white" />
        </div>

        <!-- Confirmar Senha -->
        <div class="input-wrapper">
            <span class="auth-icon">✅</span>
            <label for="password_confirmation" class="auth-label">Confirmar Senha</label>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirme a senha"
                   required class="auth-input">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-white" />
        </div>

        <!-- Tipo de Usuário -->
        <div class="mb-6">
            <label for="role" class="auth-label">Tipo de usuário</label>
            <select id="role" name="role" required
                class="w-full bg-blue-900 border border-white text-white px-3 py-2 focus:outline-none focus:ring-0 rounded-none">
                <option value="">Selecione...</option>
                <option value="aluno">Aluno</option>
                <option value="professor">Professor</option>
                <option value="napex">NAPEx</option>
                <option value="coordenador">Coordenador</option>
            </select>
        </div>


        <!-- Ações -->
        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-white hover:text-gray-200" href="{{ route('login') }}">
                Já possui cadastro?
            </a>

            <x-primary-button class="ml-4">
                Cadastrar
            </x-primary-button>
        </div>
    </form>
@endsection
