<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Sistema') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: #fff;
        }

        .form-box {
            background-image: linear-gradient(90deg, rgb(2, 10, 221) 98.9691%, rgb(2, 10, 221) 98.9691%);
        }

        .auth-input {
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding-left: 2.5rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            width: 100%;
            font-size: 1rem;
            outline: none;
            border-radius: 0;
        }

        .auth-input:focus {
            box-shadow: none;
            border-color: white;
        }

        .auth-label {
            color: white;
            font-size: 0.875rem;
            margin-left: 2.5rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .auth-icon {
            position: absolute;
            left: 0.75rem;
            top: 65%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: white;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .custom-shadow {
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.6);
        }

    </style>
</head>
<body class="min-h-screen flex items-center justify-center font-sans">
    <div class="flex bg-white custom-shadow overflow-hidden w-full max-w-4xl">
        <!-- Lado esquerdo: imagem / logo -->
        <div class="w-1/2 bg-white p-8 flex items-center justify-center">
            <img src="{{ asset('img/site/logo-univem.png') }}" alt="Logo UNIVEM" class="max-w-xs">
        </div>

        <!-- Lado direito: formulário -->
        <div class="w-1/2 form-box text-white p-10">
            <h2 class="text-2xl font-bold mb-6 text-center">Acesso ao Sistema</h2>
            @yield('content')
        </div>
    </div>
</body>
</html>
