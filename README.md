# Sistema de Curricularização da Extensão - UNIVEM

![Badge Concluído](http://img.shields.io/static/v1?label=STATUS&message=CONCLUÍDO&color=GREEN&style=for-the-badge)
![Badge Laravel](http://img.shields.io/static/v1?label=LARAVEL&message=v11&color=red&style=for-the-badge)
![Badge PHP](http://img.shields.io/static/v1?label=PHP&message=8.2&color=blue&style=for-the-badge)
![Badge License](http://img.shields.io/static/v1?label=LICENSE&message=MIT&color=BLUE&style=for-the-badge)

## 💻 Sobre o Projeto

Este repositório contém o código-fonte do sistema desenvolvido para o gerenciamento de **Projetos Extensionistas (Curricularização da Extensão)** do **UNIVEM (Centro Universitário Eurípides de Marília)**.

O objetivo principal deste projeto é automatizar e centralizar todo o ciclo de vida das atividades extensionistas, permitindo que alunos submetam propostas, acompanhem avaliações do NAPEx e Coordenação, e enviem os relatórios de resultados finais, eliminando o uso de processos manuais e papéis.

O sistema atende a diferentes perfis de acesso: **Alunos**, **Professores/Coordenadores**, **NAPEx** e **Administradores**.

---

## ⚙️ Funcionalidades Principais

### 🎓 Para Alunos
- [x] **Submissão de Propostas:** Cadastro completo de projetos com título, cronograma, objetivos e anexos.
- [x] **Gestão de Equipe:** Sistema de convites para adicionar outros participantes ao projeto.
- [x] **Envio de Resultados:** Submissão do relatório final/parcial após a execução do projeto.
- [x] **Dashboard do Aluno:** Visão geral do status de todos os projetos (Em análise, Aprovado, Ajustes Necessários).
- [x] **Feedback:** Visualização detalhada dos pareceres e motivos de rejeição/ajuste.

### 🏛 Para NAPEx e Coordenadores
- [x] **Fluxo de Aprovação:** Ferramentas para avaliar propostas e relatórios (Aprovar, Reprovar ou Solicitar Correção).
- [x] **Gerenciamento de Prazos:** Controle de datas de início e fim das atividades.
- [x] **Relatórios PDF:** Geração automática de PDFs das propostas e relatórios finais.
- [x] **Exportação em Lote:** Funcionalidade para gerar ZIP com múltiplos PDFs de uma vez para arquivamento.
- [x] **Painéis Administrativos:** Dashboards com métricas e listagens filtráveis.

### 🔔 Transversais
- [x] **Notificações:** Sistema de alertas internos sobre mudanças de status e novos convites.
- [x] **Autenticação Segura:** Controle de acesso baseado em roles (funções) e permissões.

---

## 🛠 Tecnologias Utilizadas

Este projeto foi construído utilizando uma stack moderna e robusta:

### Back-end
- **[PHP 8.2+](https://www.php.net/)**
- **[Laravel 11](https://laravel.com/)** - Framework principal.
- **MySQL** - Banco de dados relacional.
- **Redis** - Para gerenciamento de filas e cache (via Sail).

### Front-end
- **[Blade Templates](https://laravel.com/docs/blade)** - Motor de templates do Laravel.
- **[Tailwind CSS](https://tailwindcss.com/)** - Estilização utilitária e responsiva.
- **[Alpine.js](https://alpinejs.dev/)** - Interatividade leve no front-end.
- **Vite** - Build tool para otimização de assets.

### Ferramentas & DevOps
- **Docker & Laravel Sail** - Ambiente de desenvolvimento containerizado.
- **Git & GitHub** - Versionamento de código.
- **Mailpit** - Testes de envio de e-mail local.

---

## 🚀 Como Rodar o Projeto

### Pré-requisitos
Certifique-se de ter instalado em sua máquina:
* [Git](https://git-scm.com/)
* [Docker Desktop](https://www.docker.com/products/docker-desktop) (Recomendado)
* **OU** PHP 8.2+ e Composer (caso opte por rodar sem Docker)

### Opção 1: Rodando com Docker (Laravel Sail) - Recomendado 🐳
Este método é o mais simples, pois isola todas as dependências (PHP, MySQL, Node) em containers.

1. **Clone o repositório**
   ```bash
   git clone [https://github.com/LeonardoFunai/projeto-estagio-univem.git](https://github.com/LeonardoFunai/projeto-estagio-univem.git)
   cd projeto-estagio-univem
