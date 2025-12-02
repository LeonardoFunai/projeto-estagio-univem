<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cadastro de Projetos de Extensão</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <body style="font-family: 'Roboto', sans-serif; margin: 0; padding: 0;" class="d-flex-column">

        <div style="background-color: #251c57; color: white; padding: 15px 20px; font-size: 0.85rem;">
            <div class="container d-flex justify-content-between align-items-center ps-5">
                <div>
                    <span class="me-3"><i class="bi bi-telephone"></i> (14) 2105-0800</span>
                    <a href="mailto:atendimento@univem.edu.br" class="text-white text-decoration-none">
                        <i class="bi bi-chat-dots"></i> Fale Conosco
                    </a>

                    <a href="{{ route('sobre') }}"
                        class="inline-flex items-center gap-2 text-white font-bold px-3 py-1.5 h-[36px] rounded text-sm"
                        title="Sobre o Sistema">
                        ℹ️ Sobre
                    </a>
                </div>
            </div>
        </div>

        <div style="
            background-color: #28aee3;
            clip-path: polygon(3% 0, 100% 0, 100% 100%, 0% 100%);
            padding: 5px 20px;
            margin-top: -40px;
            position: relative;
            z-index: 50;
            width: 50%;
            margin-left: auto;
            .d-flex-column {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .flex-grow-1 {
                flex: 1;
            }
        ">
            <div style="color: white;" class="container d-flex justify-content-start align-items-center ">

            @if(auth()->user()->role === 'aluno')
                <a href="{{ route('aluno.dashboard', absolute: false) }}" class="btn custom-btn me-2"> Início </a> |
            @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'napex' || str_starts_with(auth()->user()->role, 'coordenador'))
                <a href="{{ route('dashboard', absolute: false) }}" class="btn custom-btn me-2"> Início </a> |
            @endif
                <a href="{{ route('projetos.index') }}" class="btn custom-btn me-2">Projetos de Extensão </a> |



                <a href="{{ route('profile.edit') }}" class="btn custom-btn me-2">
                    {{ auth()->user()->name }}
                </a>  |

                @if(in_array(auth()->user()->role, ['admin', 'napex']) || str_starts_with(auth()->user()->role, 'coordenador'))
                    <a href="{{ route('admin.users.index') }}" class="btn custom-btn me-2">Gerenciar Usuários</a> |
                @endif
                <a href="{{ route('notifications.index') }}" 
                class="btn relative" 
                style="transition: transform 0.2s ease-in-out; display: inline-block; color: white; background: none; border: none;"
                onmouseover="this.style.transform='scale(1.15)'"
                onmouseout="this.style.transform='scale(1)'">
                    &emsp;🔔&emsp;
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        {{-- CONTADOR MENOR E MAIS BAIXO --}}
                        <span class="absolute top-px -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 border border-white rounded-full">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>|

                <a href="{{ route('convites.index') }}" 
                class="btn me-2 position-relative" 
                style="transition: transform 0.2s ease-in-out; display: inline-block; color: white; background: none; border: none;"
                onmouseover="this.style.transform='scale(1.15)'"
                onmouseout="this.style.transform='scale(1)'">
                    &emsp;✉️&emsp;
                    @if(isset($pendingInvitationsCount) && $pendingInvitationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $pendingInvitationsCount }}
                            <span class="visually-hidden">convites pendentes</span>
                        </span>
                    @endif
                </a>|

                

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn custom-btn">Sair</button>
                </form>

            </div>
        </div>


        <div class="bg-white shadow-sm sticky top-[0px] z-40 border-b py-2"> 
            <div class="container d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="d-flex align-items-center gap-4 ps-4">
                    <img src="{{ asset('img/site/logo-univem.png') }}" alt="Logo Univem" style="height:60px; width:250px;">
                    <div class="text-blue-800 fw-bold text-xl ms-5">
                        {{ $pageTitle ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <main class="container pt-2 flex-grow-1">
            {{ $slot }}
        </main>

        <footer class="text-center py-3" style="background-color: #29abe2; color: white;">
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} Centro Universitário Eurípides de Marília - UNIVEM</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
    </body>
</html>