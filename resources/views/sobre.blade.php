<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sobre o Sistema') }}
        </h2>
    </x-slot>

    <x-slot name="pageTitle">
        Sobre
    </x-slot>

    {{-- [ALTERAÇÃO] CSS da Lousa Interativa --}}
    <style>
        /* Fundo escuro cobrindo a tela */
        #blackboard-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.85); /* Fundo semi-transparente escuro */
            z-index: 9999;
            
            display: flex;
            align-items: center;
            justify-content: center;
            
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
        }

        #blackboard-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Moldura da Lousa */
        .chalk-frame {
            position: relative;
            background-color: #1e2b1e; /* Verde Lousa */
            border: 15px solid #8B4513; /* Moldura Madeira */
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.7);
            width: 800px;
            height: 500px;
            max-width: 95vw;
            max-height: 90vh;
            cursor: crosshair; /* Cursor de mira/desenho */
            overflow: hidden;
        }

        /* O Canvas onde o desenho acontece */
        #drawing-canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Botão X para fechar */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-family: sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            z-index: 10;
            transition: color 0.2s;
            user-select: none;
        }

        .close-btn:hover {
            color: #fff;
        }

        /* Instrução pequena no canto */
        .chalk-hint {
            position: absolute;
            bottom: 10px;
            left: 0;
            width: 100%;
            text-align: center;
            color: rgba(255,255,255,0.3);
            font-size: 0.8rem;
            pointer-events: none;
            font-family: 'Comic Sans MS', sans-serif;
        }
    </style>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 relative">
                
                <div class="text-center mb-10">
                    {{-- Logo com ID para o clique --}}
                    <img id="logo-trigger" src="{{ asset('img/site/univem-logo-nova.jpg') }}" alt="UNIVEM" class="h-30 mx-auto mb-6 cursor-pointer select-none" title="Clique em mim...">
                    
                    <h3 class="text-3xl font-bold text-[#251C57]">Sistema de Curricularização da Extensão</h3>
                    <p class="text-gray-500 mt-2">Versão 1.0.0</p>
                </div>

                <div class="space-y-8 text-gray-700">
                    
                    <div>
                        <h4 class="text-xl font-bold text-[#251C57] mb-3 border-b pb-2">O que é este sistema?</h4>
                        <p class="leading-relaxed">
                            Esta plataforma foi desenvolvida para centralizar e agilizar o gerenciamento de 
                            <strong>Projetos Extensionistas</strong>. O sistema permite que alunos submetam suas propostas, 
                            acompanhem avaliações e enviem relatórios de resultados, facilitando a comunicação entre 
                            discentes, orientadores, NAPEx e coordenação.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-xl font-bold text-[#251C57] mb-3 border-b pb-2">Desenvolvimento</h4>
                        <p class="leading-relaxed">
                            Este sistema foi projetado e desenvolvido por <strong>Leonardo Kenji Funai</strong> com o objetivo de otimizar 
                            os processos acadêmicos do UNIVEM, utilizando tecnologias modernas de desenvolvimento web 
                            como Laravel e Tailwind CSS.
                        </p>
                    </div>

                    {{-- [ALTERAÇÃO] Área de Redes Sociais --}}
                        <div class="flex items-center gap-6 mt-4">
                            
                            {{-- LinkedIn --}}
                            
                            <a href="https://www.linkedin.com/in/leonardo-kenji-funai-36a9a8255/" target="_blank" title="LinkedIn" class="text-gray-400 hover:text-[#0077b5] transition-colors duration-300">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9H12.909v1.632h.051c.495-.939 1.706-1.929 3.514-1.929 3.758 0 4.453 2.472 4.453 5.687v6.062zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>

                            {{-- GitHub --}}
                            <a href="https://github.com/LeonardoFunai" target="_blank" title="GitHub" class="text-gray-400 hover:text-gray-900 transition-colors duration-300">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.419-1.305.762-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                                </svg>
                            </a>

                            {{-- Instagram --}}
                            <a href="https://www.instagram.com/leonardo_kenjii?igsh=MTBlZjNhcHRyZWt1bg==" target="_blank" title="Instagram" class="text-gray-400 hover:text-[#E1306C] transition-colors duration-300">
                                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        </div>

                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('projetos.index') }}" class="inline-flex items-center px-6 py-3 bg-[#251C57] border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-blue-900 transition ease-in-out duration-150 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Voltar para Projetos
                    </a>
                </div>
                
                {{-- Dica sutil do Easter Egg --}}
                <div class="mt-8 text-center opacity-20 text-xs select-none">
                    .
                </div>

            </div>
        </div>
    </div>

    {{-- [ALTERAÇÃO] Estrutura da Lousa Interativa --}}
    <div id="blackboard-overlay">
        <div class="chalk-frame">
            <span class="close-btn" id="close-blackboard" title="Fechar">✕</span>
            
            {{-- Canvas onde se desenha --}}
            <canvas id="drawing-canvas"></canvas>
            
            <div class="chalk-hint">Arraste o mouse para desenhar</div>
        </div>
    </div>

    {{-- Script de Controle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Elementos ---
            const logo = document.getElementById('logo-trigger');
            const overlay = document.getElementById('blackboard-overlay');
            const closeBtn = document.getElementById('close-blackboard');
            const canvas = document.getElementById('drawing-canvas');
            const ctx = canvas.getContext('2d');

            // --- Variáveis de Controle do Clique ---
            let clickCount = 0;
            let clickTimer;
            const requiredClicks = 5;

            // --- Variáveis de Desenho ---
            let isDrawing = false;

            // 1. Lógica do Easter Egg (Cliques)
            logo.addEventListener('click', function() {
                clickCount++;
                console.log(`Clicks: ${clickCount}`);
                clearTimeout(clickTimer);

                if (clickCount === requiredClicks) {
                    openBlackboard();
                    clickCount = 0;
                } else {
                    clickTimer = setTimeout(() => {
                        clickCount = 0;
                    }, 1000);
                }
            });

            // 2. Abrir Lousa e Configurar Canvas
            function openBlackboard() {
                overlay.classList.add('active');
                
                // Ajusta o tamanho do canvas para o tamanho real do elemento na tela
                // Isso evita distorção do traço
                const rect = canvas.parentElement.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;

                // Estilo do "Giz"
                ctx.strokeStyle = '#ffffff'; // Cor branca
                ctx.lineWidth = 3;           // Espessura
                ctx.lineCap = 'round';       // Ponta arredondada
                ctx.lineJoin = 'round';
            }

            // 3. Fechar Lousa
            function closeBlackboard() {
                overlay.classList.remove('active');
                // Limpa o desenho ao fechar (opcional)
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }

            closeBtn.addEventListener('click', closeBlackboard);
            
            // Fecha se clicar fora da lousa (no fundo escuro)
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeBlackboard();
                }
            });

            // 4. Lógica de Desenho no Canvas
            function startDrawing(e) {
                isDrawing = true;
                draw(e); // Desenha o ponto inicial
            }

            function stopDrawing() {
                isDrawing = false;
                ctx.beginPath(); // Reseta o caminho para não conectar linhas distantes
            }

            function draw(e) {
                if (!isDrawing) return;

                // Calcula a posição do mouse relativa ao canvas
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                ctx.lineTo(x, y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(x, y);
            }

            // Eventos do Mouse
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
        });
    </script>
</x-app-layout>