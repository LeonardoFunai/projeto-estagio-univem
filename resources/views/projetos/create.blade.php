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
                <textarea name="introducao" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <small class="block mb-2 text-gray-600">(O que fazer, como fazer e carga horária)</small>

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
                            @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $mes)
                                <option value="{{ $mes }}">{{ $mes }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Cronograma</button>

                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4"></textarea>

                <label class="block mb-2">Arquivo (opcional)</label>
                <input type="file" name="arquivo" class="w-full border-gray-300 rounded-md mb-6">
            </fieldset>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Salvar Projeto</button>
        </form>
    </div>

    <script>
        let professorCount = 1;
        let alunoCount = 1;
        let atividadeCount = 1;
        let cronogramaCount = 1;

        document.getElementById('add-professor')?.addEventListener('click', () => {
            if (professorCount < 9) {
                const div = document.createElement('div');
                div.classList.add('mb-4');
                div.innerHTML = `
                    <input type="text" name="professores[${professorCount}][nome]" class="form-control mb-2" placeholder="Nome do professor" required>
                    <input type="email" name="professores[${professorCount}][email]" class="form-control mb-2" placeholder="Email (opcional)">
                    <input type="text" name="professores[${professorCount}][area]" class="form-control mb-2" placeholder="Área (opcional)">
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                document.getElementById('professores-wrapper').appendChild(div);
                professorCount++;
            }
        });

        document.getElementById('add-aluno')?.addEventListener('click', () => {
            if (alunoCount < 9) {
                const div = document.createElement('div');
                div.classList.add('mb-4');
                div.innerHTML = `
                    <input type="text" name="alunos[${alunoCount}][nome]" class="form-control mb-2" placeholder="Nome do aluno" required>
                    <input type="text" name="alunos[${alunoCount}][ra]" class="form-control mb-2" placeholder="RA" required>
                    <input type="text" name="alunos[${alunoCount}][curso]" class="form-control mb-2" placeholder="Curso" required>
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                document.getElementById('alunos-wrapper').appendChild(div);
                alunoCount++;
            }
        });

        document.getElementById('add-atividade')?.addEventListener('click', () => {
            const div = document.createElement('div');
            div.classList.add('mb-4');
            div.innerHTML = `
                <textarea name="atividades[${atividadeCount}][o_que_fazer]" class="form-control mb-2" placeholder="O que fazer?" required></textarea>
                <textarea name="atividades[${atividadeCount}][como_fazer]" class="form-control mb-2" placeholder="Como fazer?" required></textarea>
                <input type="number" name="atividades[${atividadeCount}][carga_horaria]" class="form-control mb-2" placeholder="Carga horária" required>
                <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
            `;
            document.getElementById('atividades-wrapper').appendChild(div);
            atividadeCount++;
        });

        document.getElementById('add-cronograma')?.addEventListener('click', () => {
            const div = document.createElement('div');
            div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mb-4');
            div.innerHTML = `
                <input type="text" name="cronograma[${cronogramaCount}][atividade]" class="form-control" placeholder="Título da Atividade" required>
                <select name="cronograma[${cronogramaCount}][mes]" class="form-control" required>
                    <option value="">Selecione o mês</option>
                    ${['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}">${m}</option>`).join('')}
                </select>
                <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
            `;
            document.getElementById('cronograma-wrapper').appendChild(div);
            cronogramaCount++;
        });

        document.getElementById('form-projeto').addEventListener('submit', function (e) {
            const inicio = document.getElementById('data_inicio').value;
            const fim = document.getElementById('data_fim').value;
            if (!inicio || !fim || new Date(inicio) > new Date(fim)) {
                e.preventDefault();
                alert('A data de início deve ser anterior ou igual à data de fim.');
            }
        });
    </script>
</x-app-layout>
