@extends('layouts.auth')

@section('content')
    <!-- Status da Sessão -->
    <x-auth-session-status class="mb-4 text-white" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

    <!-- Campo Email -->
    <div class="input-wrapper">
        <span class="auth-icon">👤</span>
        <label for="email" class="auth-label">Email</label>
        <input id="email" type="email" name="email" placeholder="Email"
            value="{{ old('email') }}" required autofocus class="auth-input">
    </div>

    <!-- Campo Senha -->
    <div class="input-wrapper">
        <span class="auth-icon">🔒</span>
        <label for="password" class="auth-label">Senha</label>
        <input id="password" type="password" name="password" placeholder="Senha"
            required class="auth-input">
    </div>



        <!-- Lembrar de Mim -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-white">Lembrar de mim</span>
            </label>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-6 gap-3">
            <div class="flex gap-3">
                <!-- Botão de login (verde) -->
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150">
                    Entrar
                </button>

                <!-- Botão de registro -->
                <a href="{{ route('register') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    Cadastre-se
                </a>
            </div>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-white hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                    href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif
        </div>
    </form>
@endsection
