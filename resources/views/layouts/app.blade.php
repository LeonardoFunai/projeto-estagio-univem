<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Projetos de Extensão</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body style="font-family: 'Roboto', sans-serif;     margin: 0;padding: 0;">

    <!-- Barra superior institucional (ajustada) -->
    <div style="background-color: #251c57; color: white; padding: 5px 20px; font-size: 0.85rem;">
        <div class="container d-flex justify-content-start align-items-center">
            <span class="me-3"><i class="bi bi-telephone"></i> (14) 2105-0800</span>
            <span><i class="bi bi-chat-dots"></i> Fale Conosco</span>
        </div>
    </div>

    <!-- Cabeçalho com logo + botões -->
    <div class="bg-white shadow-sm">
        <div class="container d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center">
                <img src="{{ asset('img/site/logo.jpg') }}" alt="Logo Univem" style="height: 60px; margin-right: 10px;">
                <div>
                    <h2 style="margin: 0; color: blue;">UNIVEM</h2>
                    <small style="color: gray;">Centro Universitário Eurípedes de Marília</small>
                </div>
            </div>

            <!-- Botões de navegação -->
            <div>
                <a href="{{ route('projetos.index') }}" class="btn btn-outline-primary me-2">Lista de Propostas</a>
                <a href="{{ route('projetos.create') }}" class="btn btn-primary">Nova Proposta</a>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <main style="padding: 20px;">
        @yield('content')
    </main>

    <!-- Rodapé -->
    <footer class="text-center py-3 bg-light mt-5">
        <div class="container">
            <p class="mb-0">&copy; Copyright © {{ date('Y') }} - Centro Universitário Eurípides de Marília - UNIVEM</p>
        </div>
    </footer>

</body>
</html>
