import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Executa o script apenas quando a página inteira for carregada
document.addEventListener('DOMContentLoaded', function() {

    // Tenta encontrar os elementos da página de login
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    // SÓ executa o código se AMBOS os elementos existirem nesta página
    if (togglePassword && passwordInput) {

        togglePassword.addEventListener('click', function () {
            // Alterna o tipo do campo entre 'password' e 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Alterna o ícone do olho
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
});