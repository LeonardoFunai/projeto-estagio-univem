<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Projetos de Extensão</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
</head>
<body>
    <header>
        <div class="container" style="display: flex; align-items: center;">
            <img src="{{ asset('img//univem-logo.jpg ') }}" alt="Logo Univem" style="height: 50px; margin-right: 15px;">
            <div>
                <h1 style="margin: 0;">Univem - Centro Universitário Eurípedes de Marília</h1>
            </div>
        </div>
    </header>

    <main style="padding: 20px;">
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} - Universidade XYZ</p>
        </div>
    </footer>
</body>
</html>
