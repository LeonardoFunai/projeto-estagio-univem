# Sistema de Gerenciamento de Propostas de Atividades de Extensão - UNIVEM

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss)

Sistema robusto para cadastro, avaliação e gerenciamento de propostas de atividades de extensão curricular, desenvolvido com a stack TALL (Tailwind, Alpine.js, Laravel, Livewire).

## 📋 Tabela de Conteúdos

1.  [Sobre o Projeto](#-sobre-o-projeto)
2.  [🚀 Principais Funcionalidades](#-principais-funcionalidades)
3.  [🛠️ Tecnologias Utilizadas](#-tecnologias-utilizadas)
4.  [⚙️ Pré-requisitos](#-pré-requisitos)
5.  [🏁 Instalação e Configuração](#-instalação-e-configuração)
6.  [🔑 Contas de Acesso Padrão](#-contas-de-acesso-padrão)
7.  [🧪 Executando os Testes](#-executando-os-testes)
8.  [🤝 Contribuições](#-contribuições)

## 📖 Sobre o Projeto

Este sistema foi projetado para digitalizar e otimizar o fluxo de submissão e avaliação de propostas de atividades de extensão acadêmica. Ele atende a diferentes perfis de usuários, cada um com suas permissões e responsabilidades específicas dentro do ciclo de vida de uma proposta.

**O fluxo principal do sistema é:**
1.  **Criação:** Um `aluno` ou `professor` cria uma nova proposta, preenchendo todos os detalhes, atividades e cronogramas.
2.  **Submissão:** Após a revisão, a proposta é enviada para avaliação.
3.  **Avaliação em Duas Etapas:**
    * O **NAPEx** (Núcleo de Apoio à Pesquisa e Extensão) realiza a primeira avaliação.
    * O **Coordenador de Curso** realiza a segunda avaliação.
4.  **Aprovação/Rejeição:** O projeto é oficialmente aprovado somente após o parecer positivo de ambas as instâncias. Em caso de rejeição, o histórico fica registrado e a proposta pode ser ajustada e reenviada.

O sistema utiliza o sistema de Policies do Laravel para um controle de acesso granular e seguro, garantindo que cada usuário só possa realizar as ações permitidas para seu perfil.

## 🚀 Principais Funcionalidades

-   **Controle de Acesso Baseado em Papéis (RBAC):** Permissões distintas para Alunos, Professores, Coordenadores e NAPEx.
-   **Fluxo de Aprovação Completo:** Múltiplas etapas de avaliação com registro de pareceres e histórico de rejeições.
-   **Formulários Dinâmicos:** Adição dinâmica de múltiplos alunos, professores, atividades e itens de cronograma.
-   **Filtros Avançados:** Listagem de propostas com filtros por status, datas, carga horária e múltiplos cursos simultaneamente.
-   **Geração de Relatórios em PDF:**
    -   Exportação de um relatório geral com a lista de propostas filtradas.
    -   Geração de um PDF detalhado para cada proposta individual.
-   **Interface Responsiva:** Construída com Tailwind CSS para uma ótima experiência em desktops e dispositivos móveis.

## 🛠️ Tecnologias Utilizadas

-   **Backend:** Laravel 11
-   **Frontend:** Blade, Tailwind CSS, JavaScript
-   **Banco de Dados:** MySQL / MariaDB
-   **Geração de PDF:** `barryvdh/laravel-dompdf`

## ⚙️ Pré-requisitos

Antes de começar, garanta que você tenha um ambiente de desenvolvimento local configurado com:
-   [PHP](https://www.php.net/manual/pt_BR/install.php) >= 8.2
-   [Composer](https://getcomposer.org/download/)
-   [Node.js](https://nodejs.org/en/) & NPM
-   Um servidor de banco de dados (ex: MySQL, MariaDB)

## 🏁 Instalação e Configuração

Siga os passos abaixo para configurar o ambiente de desenvolvimento localmente.

**1. Clonar o Repositório**
```bash
git clone https://github.com/LeonardoFunai/projeto-estagio-univem.git
cd projeto-estagio-univem

**2. Instalar as Dependências do PHP**
```bash
composer install

3. Configurar o Arquivo de Ambiente
Copie o arquivo de exemplo para criar seu próprio arquivo de configuração local.

```bash
cp .env.example .env

4. Configurar o Banco de Dados no .env
Abra o arquivo .env recém-criado e atualize as variáveis de banco de dados (DB_*) com as credenciais do seu servidor local.

```Shell
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=univem_projetos
DB_USERNAME=root
DB_PASSWORD=

Importante: Crie um banco de dados vazio com o nome que você definiu em DB_DATABASE (neste exemplo, univem_projetos).

5. Gerar a Chave da Aplicação

```Bash
php artisan key:generate

6. Executar as Migrações e Seeders
Este comando irá criar todas as tabelas no banco de dados e popular com dados iniciais (usuários, cursos, etc.).

```Bash
php artisan migrate:fresh --seed

7. Instalar as Dependências do NPM e Compilar os Assets

```Bash
npm install
npm run dev

8. Iniciar o Servidor de Desenvolvimento

```Bash
php artisan serve

Pronto! 🎉 A aplicação estará acessível em seu navegador no endereço: http://127.0.0.1:8000.