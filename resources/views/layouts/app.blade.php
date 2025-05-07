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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    .custom-btn {
        border: none !important;
        background: none !important;
        color: white !important;
        transition: background-color 0.3s, color 0.3s;
    }

    .custom-btn:hover {
        background-color: #28aee3 !important;
        color: #251c57 !important;
    }
    [x-cloak] { display: none !important; }

</style>

</head>
<body style="font-family: 'Roboto', sans-serif; margin: 0; padding: 0;">

    <!-- Barra roxa escura superior -->
    <div style="background-color: #251c57; color: white; padding: 15px 20px; font-size: 0.85rem;">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="me-3"><i class="bi bi-telephone"></i> (14) 2105-0800</span>
                <span><i class="bi bi-chat-dots"></i> Fale Conosco</span>
            </div>

        </div>
    </div>


<!-- Faixa azul clara inclinada -->
<div style="
    background-color: #28aee3;
    clip-path: polygon(3% 0, 100% 0, 100% 100%, 0% 100%);
    padding: 5px 20px;
    margin-top: -40px;
    position: relative;
    z-index: 20;
    width: 50%;
    margin-left: auto;


">
    <div style="color: white;" class="container d-flex justify-content-start align-items-center ">
        <a href="{{ route('projetos.index') }}" class="btn custom-btn me-2">Lista de Propostas</a> |

        @if(auth()->user()->role == 'aluno')
            <a href="{{ route('projetos.create') }}" class="btn custom-btn me-2">Nova Proposta</a>  |
        @endif

        <a href="{{ route('profile.edit') }}" class="btn custom-btn me-2">
            {{ Auth::user()->name }}
        </a>  |

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn custom-btn">Sair</button>
        </form>

    </div>
</div>


<!-- Logo e navegação principal -->
<div class="bg-white shadow-sm" style="position: relative;">
    <!-- removido z-index -->
    <div class="container d-flex align-items-center justify-content-start py-3">
        <img src="{{ asset('img/site/logo-univem.png') }}" alt="Logo Univem" style="height:60px; width:250px; margin-right: 5px;">
    </div>
</div>



    <!-- Conteúdo principal -->
    <main class="container my-4">
        {{ $slot }}
    </main>

    <!-- Rodapé -->
    <footer class="text-center py-3" style="background-color: #29abe2; color: white;">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Centro Universitário Eurípides de Marília - UNIVEM</p>
        </div>
    </footer>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
