<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cadastrar Projeto de Extensão') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-blue-800 mb-8">Cadastro de Projeto de Extensão</h1>

        <form id="form-projeto" action="{{ route('projetos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Introdução</legend>

                <label class="block mb-2">Título do Projeto:</label>
                <input type="text" name="titulo" class="w-full border-gray-300 rounded-md mb-4" placeholder="Título do Projeto" required>

                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" class="w-full border-gray-300 rounded-md mb-4" placeholder="Fevereiro a Junho de 2025." required>

                <label class="block mb-2">Professor(es) envolvidos:</label>
                <div id="professores-wrapper">
                    <div class="mb-4">
                        <input type="text" name="professores[0][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do professor" required>
                        <input type="email" name="professores[0][email]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Email (opcional)">
                        <input type="text" name="professores[0][area]" class="w-full border-gray-300 rounded-md" placeholder="Área (opcional)">
                    </div>
                </div>
                <button type="button" id="add-professor" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Professor</button>

                <label class="block mb-2">Alunos envolvidos / R.A / Curso:</label>
                <div id="alunos-wrapper">
                    <div class="mb-4">
                        <input type="text" name="alunos[0][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do aluno" required>
                        <input type="text" name="alunos[0][ra]" class="w-full border-gray-300 rounded-md mb-2" placeholder="RA" required>
                        <input type="text" name="alunos[0][curso]" class="w-full border-gray-300 rounded-md" placeholder="Curso" required>
                    </div>
                </div>
                <button type="button" id="add-aluno" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Aluno</button>

                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-4" placeholder="População em Geral"></textarea>

                <label class="block mb-2">Data de Início:</label>
                <input type="date" name="data_inicio" id="data_inicio" class="w-full border-gray-300 rounded-md mb-4" required>

                <label class="block mb-2">Data de Término:</label>
                <input type="date" name="data_fim" id="data_fim" class="w-full border-gray-300 rounded-md mb-4" required>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Detalhes do Projeto</legend>

                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao" placeholder="descreva os problemas observados em uma organização ou sociedade, a vantagem de fazer o projeto tanto para a equipe (alunos) e sociedade. 
                " class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral" class="w-full border-gray-300 rounded-md mb-4" placeholder="Geral: o que pretende atingir. Um dos objetivos é cumprir a carga horária da atividade de extensão curricularizada"></textarea>

                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa" class="w-full border-gray-300 rounded-md mb-4" placeholder=" (Justifique o porquê de desenvolver o projeto, inclusive pode usar material de apoio, leis, literatura – para argumentar a justificativa).
                "></textarea>

                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia" class="w-full border-gray-300 rounded-md mb-4" placeholder="(descrever brevemente como o projeto será conduzido, controle, treinamento e custos, planos para emergência, ferramentas utilizadas)."></textarea>

                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <small class="block mb-2 text-gray-600">
                    (Relacione todas as atividades que serão desenvolvidas - o que vai fazer: o que fazer, como fazer e carga horária).
                </small>

                <div id="atividades-wrapper">
                    <div class="mb-4">
                        <textarea name="atividades[0][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required></textarea>
                        <textarea name="atividades[0][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required></textarea>
                        <input type="number" name="atividades[0][carga_horaria]" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required>
                    </div>
                </div>
                <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade</button>

                <label class="block mb-2 text-lg font-semibold text-blue-700">Cronograma</label>
<div id="cronograma-wrapper">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <input type="text" name="cronograma[0][atividade]" class="form-control" placeholder="Título da Atividade" required>
        <select name="cronograma[0][mes]" class="form-control" required>
            <option value="">Selecione o mês</option>
            <option value="Janeiro">Janeiro</option>
            <option value="Fevereiro">Fevereiro</option>
            <option value="Março">Março</option>
            <option value="Abril">Abril</option>
            <option value="Maio">Maio</option>
            <option value="Junho">Junho</option>
            <option value="Julho">Julho</option>
            <option value="Agosto">Agosto</option>
            <option value="Setembro">Setembro</option>
            <option value="Outubro">Outubro</option>
            <option value="Novembro">Novembro</option>
            <option value="Dezembro">Dezembro</option>
        </select>
    </div>
</div>
<button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Cronograma</button>


                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <h2 class="text-lg font-bold text-blue-700 mb-4 mt-8">Parecer do Núcleo de Apoio à Pesquisa e Extensão (NAPEx)</h2>


                <label class="block mb-2">Número do Projeto</label>
                <input type="text" name="numero_projeto" class="w-full border-gray-300 rounded-md mb-4">

                <label class="block mb-2">Data recebimento da Proposta de Projeto de Extensão pelo NAPEx</label>
                <input type="date" name="data_recebimento_napex" class="w-full border-gray-300 rounded-md mb-4">

                <label class="block mb-2">Data de Encaminhamento da Proposta do Projeto de Extensão para os devidos pareceres</label>
                <input type="date" name="data_encaminhamento_parecer" class="w-full border-gray-300 rounded-md mb-4">

                <label class="block mb-2">Aprovação do NAPEx:</label>
                <div class="mb-4">
                    <label><input type="radio" name="aprovado_napex" value="sim"> Sim</label>
                    <label class="ml-4"><input type="radio" name="aprovado_napex" value="nao"> Não</label>
                </div>

                <label class="block mb-2">Exposição de motivos (NAPEx)</label>
                <textarea name="motivo_napex" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <h2 class="text-lg font-bold text-blue-700 mb-4 mt-8">Parecer do Coordenador de Curso</h2>


                <label class="block mb-2">Aprovação do Coordenador:</label>
                <div class="mb-4">
                    <label><input type="radio" name="aprovado_coordenador" value="sim"> Sim</label>
                    <label class="ml-4"><input type="radio" name="aprovado_coordenador" value="nao"> Não</label>
                </div>

                <label class="block mb-2">Exposição de motivos (Coordenador)</label>
                <textarea name="motivo_coordenador" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">Data do parecer do Coordenador</label>
                <input type="date" name="data_parecer_coordenador" class="w-full border-gray-300 rounded-md mb-4">
            </fieldset>

            <label class="block mb-2">Arquivo (opcional)</label>
            <input type="file" name="arquivo" class="w-full border-gray-300 rounded-md mb-6">

            <label class="block mb-2">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-md mb-6" required>
                <option value="editando" selected>Editando</option>
                <option value="entregue">Entregue</option>
            </select>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Salvar Projeto</button>
        </form>
    </div>

    <script>

let cronogramaWrapper = document.getElementById('cronograma-wrapper');
    let addCronogramaBtn = document.getElementById('add-cronograma');
    let cronogramaCount = 1;

    addCronogramaBtn.addEventListener('click', function () {
        const div = document.createElement('div');
        div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mb-4');
        div.innerHTML = `
            <input type="text" name="cronograma[${cronogramaCount}][atividade]" class="form-control" placeholder="Título da Atividade" required>
            <select name="cronograma[${cronogramaCount}][mes]" class="form-control" required>
                <option value="">Selecione o mês</option>
                <option value="Janeiro">Janeiro</option>
                <option value="Fevereiro">Fevereiro</option>
                <option value="Março">Março</option>
                <option value="Abril">Abril</option>
                <option value="Maio">Maio</option>
                <option value="Junho">Junho</option>
                <option value="Julho">Julho</option>
                <option value="Agosto">Agosto</option>
                <option value="Setembro">Setembro</option>
                <option value="Outubro">Outubro</option>
                <option value="Novembro">Novembro</option>
                <option value="Dezembro">Dezembro</option>
            </select>
            <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
        `;
        cronogramaWrapper.appendChild(div);
        cronogramaCount++;
    });
        let professoresWrapper = document.getElementById('professores-wrapper');
        let addProfessorBtn = document.getElementById('add-professor');
        let professorCount = 1;

        addProfessorBtn.addEventListener('click', function () {
            if (professorCount < 9) {
                const div = document.createElement('div');
                div.classList.add('professor-group');
                div.innerHTML = `
                    <input type="text" name="professores[${professorCount}][nome]" class="form-control mb-2" placeholder="Nome do professor" required>
                    <input type="email" name="professores[${professorCount}][email]" class="form-control mb-2" placeholder="Email (opcional)">
                    <input type="text" name="professores[${professorCount}][area]" class="form-control mb-2" placeholder="Área (opcional)">
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                professoresWrapper.appendChild(div);
                professorCount++;
            }
        });

        let alunosWrapper = document.getElementById('alunos-wrapper');
        let addAlunoBtn = document.getElementById('add-aluno');
        let alunoCount = 1;

        addAlunoBtn.addEventListener('click', function () {
            if (alunoCount < 9) {
                const div = document.createElement('div');
                div.classList.add('aluno-group');
                div.innerHTML = `
                    <input type="text" name="alunos[${alunoCount}][nome]" class="form-control mb-2" placeholder="Nome do aluno" required>
                    <input type="text" name="alunos[${alunoCount}][ra]" class="form-control mb-2" placeholder="RA" required>
                    <input type="text" name="alunos[${alunoCount}][curso]" class="form-control mb-2" placeholder="Curso" required>
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                alunosWrapper.appendChild(div);
                alunoCount++;
            }
        });

        let atividadesWrapper = document.getElementById('atividades-wrapper');
        let addAtividadeBtn = document.getElementById('add-atividade');
        let atividadeCount = 1;

        addAtividadeBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.classList.add('atividade-group');
            div.innerHTML = `
                <textarea name="atividades[${atividadeCount}][o_que_fazer]" class="form-control mb-2" placeholder="O que fazer?" required></textarea>
                <textarea name="atividades[${atividadeCount}][como_fazer]" class="form-control mb-2" placeholder="Como fazer?" required></textarea>
                <input type="number" name="atividades[${atividadeCount}][carga_horaria]" class="form-control mb-2" placeholder="Carga horária" required>
                <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
            `;
            atividadesWrapper.appendChild(div);
            atividadeCount++;
        });

        document.getElementById('form-projeto').addEventListener('submit', function (e) {
            const inicio = document.getElementById('data_inicio').value;
            const fim = document.getElementById('data_fim').value;

            if (!inicio || !fim || new Date(inicio) > new Date(fim)) {
                e.preventDefault();
                alert('A data de início deve ser anterior ou igual à data de fim.');
                return;
            }
        });
    </script>
</x-app-layout>
