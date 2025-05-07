<!-- Página de edição do projeto de extensão  -->

<x-app-layout>
<!-- Cabeçalho da página -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Projeto de Extensão') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-blue-800 mb-8">Editar Projeto de Extensão</h1>
        <!-- Definição de variáveis de permissão conforme papel do usuário -->
        @php
            $userRole = auth()->user()->role;
            $disableAlunoFields = in_array($userRole, ['coordenador', 'napex']);
            $disableNapexFields = in_array($userRole, ['coordenador', 'aluno']);
            $disableCoordenadorFields = !($userRole === 'coordenador');
        @endphp
        <!-- Botões de enviar projeto ou voltar para edição (visíveis apenas para aluno) -->
        @if ($userRole === 'aluno')
            @if ($projeto->status === 'editando')
                <form method="POST" action="{{ route('projetos.enviar', $projeto->id) }}" class="mb-4">
                    @csrf
                    <button type="submit" class="btn btn-primary">Enviar Projeto</button>
                </form>
            @elseif ($projeto->status === 'entregue' && !$projeto->napex_aprovado && !$projeto->coordenacao_aprovado)
                <form method="POST" action="{{ route('projetos.voltar', $projeto->id) }}" class="mb-4">
                    @csrf
                    <button type="submit" class="btn btn-warning">Voltar para Edição</button>
                </form>
            @endif
        @endif
        <!-- Mensagem de erro, se houver -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário de edição do projeto -->
        <form id="form-projeto" action="{{ route('projetos.update', $projeto->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Introdução</legend>

                <!-- Campo: Título do Projeto -->
                <label class="block mb-2">Título do Projeto:</label>
                <input type="text" name="titulo" value="{{ $projeto->titulo }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}" 
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                <!-- Campo: Período  -->
                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" value="{{ $projeto->periodo }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
                    
                    <!-- Campo: Professor(es) envolvidos -->
                    <label class="block mb-2">Professor(es) envolvidos:</label>
                    <div id="professores-wrapper">
                        @foreach ($projeto->professores as $index => $prof)
                            <div class="mb-4 flex items-center gap-4">
                                <select name="professores[{{ $index }}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                                    <option value="">-- Selecione um professor --</option>
                                    @foreach ($professores as $professor)
                                        <option value="{{ $professor->id }}" {{ $professor->name === $prof->nome ? 'selected' : '' }}>
                                            {{ $professor->name }} ({{ $professor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="professores[{{ $index }}][area]" value="{{ $prof->area }}" class="w-full border-gray-300 rounded-md mb-2" placeholder="Área (opcional)">
                                <button type="button" onclick="this.parentNode.remove()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">Remover</button>
                            </div>
                        @endforeach
                    </div>


                    <!-- Botão para adicionar professor (se permitido) -->  
                    @if(!$disableAlunoFields)
                        <button type="button" id="add-professor" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Professor</button>
                    @endif

                <!-- Campo: Alunos envolvidos -->
                <label class="block mb-2">Alunos envolvidos / R.A / Curso:</label>
                <div id="alunos-wrapper">
                    @foreach ($projeto->alunos as $index => $aluno)
                        <div class="mb-4">
                            <input type="text" name="alunos[{{ $index }}][nome]" value="{{ $aluno->nome }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
                            <input type="text" name="alunos[{{ $index }}][ra]" value="{{ $aluno->ra }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
                            <input type="text" name="alunos[{{ $index }}][curso]" value="{{ $aluno->curso }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
                        </div>
                    @endforeach
                </div>

                <!-- Botão para adicionar aluno (se permitido) -->
                @if(!$disableAlunoFields)
                    <button type="button" id="add-aluno" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Aluno</button>
                @endif

                <!-- Campo: Público Alvo -->
                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->publico_alvo }}</textarea>

                <!-- Campos: Data     -->
                <label class="block mb-2">Data de Início:</label>
                <input type="date" name="data_inicio" value="{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('Y-m-d') }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                <label class="block mb-2">Data de Término:</label>
                <input type="date" name="data_fim" value="{{ \Carbon\Carbon::parse($projeto->data_fim)->format('Y-m-d') }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Detalhes do Projeto</legend>

                <!-- Campo: Introdução -->
                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->introducao }}</textarea>

                <!-- Campo: Objetivos do Projeto -->
                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->objetivo_geral }}</textarea>

                <!-- Campo: Justificativa -->
                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->justificativa }}</textarea>

                <!-- Campo: Metodologia -->
                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->metodologia }}</textarea>

                <!-- Campo: Atividades a serem desenvolvidas -->
                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <div id="atividades-wrapper">
                    @foreach ($projeto->atividades as $index => $atividade)
                        <div class="mb-4">
                            <textarea name="atividades[{{ $index }}][o_que_fazer]"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $atividade->o_que_fazer }}</textarea>

                            <textarea name="atividades[{{ $index }}][como_fazer]"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $atividade->como_fazer }}</textarea>

                            <input type="number" name="atividades[{{ $index }}][carga_horaria]"
                                value="{{ $atividade->carga_horaria }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>
                        </div>
                    @endforeach
                </div>

                <!-- Botão: Adicionar nova atividade -->
                @if(!$disableAlunoFields)
                    <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                        + Adicionar Atividade
                    </button>
                @endif

                <!-- Campo: Cronograma -->
                <label class="block mb-2">6. Cronograma</label>
                <div id="cronograma-wrapper">
                    @foreach ($projeto->cronogramas as $index => $cronograma)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <input type="text" name="cronograma[{{ $index }}][atividade]" value="{{ $cronograma->atividade }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>

                            <select name="cronograma[{{ $index }}][mes]"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'disabled' : '' }}>
                                <option value="">Selecione o mês</option>
                                @foreach (['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $mes)
                                    <option value="{{ $mes }}" {{ $cronograma->mes == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <!-- Botão: Adicionar novo cronograma -->
                @if(!$disableAlunoFields)
                    <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                        + Adicionar Cronograma
                    </button>
                @endif

                <!-- Campo: Recursos Necessários -->
                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->recursos }}</textarea>

                <!-- Campo: Resultados Esperados -->
                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->resultados_esperados }}</textarea>

                <!-- Campo: Upload de Arquivo -->
                <label class="block mb-2">Arquivo (opcional)</label>
                @if($userRole === 'aluno')
                    <input type="file" name="arquivo" class="w-full border-gray-300 rounded-md mb-6">
                @else
                    <input type="file" class="w-full border-gray-300 rounded-md mb-6 opacity-50" disabled>
                @endif
            </fieldset>



            <div class="flex justify-center gap-4 mb-8">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                    Atualizar Projeto
                </button>
                <a href="{{ route('projetos.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                    Voltar
                </a>
            </div>
        </form>
    </div>

    <script>
        let professorCount = document.querySelectorAll('#professores-wrapper > div').length || 1;
        let alunoCount = document.querySelectorAll('#alunos-wrapper > div').length || 1;
        let atividadeCount = document.querySelectorAll('#atividades-wrapper > div').length || 1;
        let cronogramaCount = document.querySelectorAll('#cronograma-wrapper > div').length || 1;

        // Adiciona títulos aos blocos já existentes ao carregar
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#professores-wrapper > div').forEach((div, index) => {
                if (!div.querySelector('h4')) {
                    const title = document.createElement('h4');
                    title.classList.add('font-semibold', 'mb-2');
                    title.textContent = `Professor ${index + 1}`;
                    div.prepend(title);
                }
            });
            document.querySelectorAll('#alunos-wrapper > div').forEach((div, index) => {
                if (!div.querySelector('h4')) {
                    const title = document.createElement('h4');
                    title.classList.add('font-semibold', 'mb-2');
                    title.textContent = `Aluno ${index + 1}`;
                    div.prepend(title);
                }
            });
            document.querySelectorAll('#atividades-wrapper > div').forEach((div, index) => {
                if (!div.querySelector('h4')) {
                    const title = document.createElement('h4');
                    title.classList.add('font-semibold', 'mb-2');
                    title.textContent = `Atividade ${index + 1}`;
                    div.prepend(title);

                    const labels = div.querySelectorAll('textarea, input[type="number"]');
                    if (labels.length === 3) {
                        const [oq, como, carga] = labels;
                        oq.insertAdjacentHTML('beforebegin', '<label class="block mb-1">O que fazer</label>');
                        como.insertAdjacentHTML('beforebegin', '<label class="block mb-1">Como fazer</label>');
                        carga.insertAdjacentHTML('beforebegin', '<label class="block mb-1">Carga horária (horas)</label>');
                    }
                }
            });
        });

        const professorOptions = `
            <option value="">-- Selecione um professor --</option>
            ${Array.from(document.querySelector('select[name="professores[0][id]"]').options)
                .slice(1)
                .map(option => `<option value="${option.value}">${option.text}</option>`)
                .join('')}
        `;

        document.getElementById('add-professor')?.addEventListener('click', () => {
            if (professorCount < 9) {
                const div = document.createElement('div');
                div.classList.add('mb-4');
                div.innerHTML = `
                    <h4 class="font-semibold mb-2">Professor ${professorCount + 1}</h4>
                    <select name="professores[${professorCount}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                        ${professorOptions}
                    </select>
                    <input type="text" name="professores[${professorCount}][area]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Área (opcional)">
                    <button type="button" onclick="this.parentNode.remove()" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
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
                    <h4 class="font-semibold mb-2">Aluno ${alunoCount + 1}</h4>
                    <input type="text" name="alunos[${alunoCount}][nome]" class="form-control mb-2" placeholder="Nome do aluno" required>
                    <input type="text" name="alunos[${alunoCount}][ra]" class="form-control mb-2" placeholder="RA" required>
                    <input type="text" name="alunos[${alunoCount}][curso]" class="form-control mb-2" placeholder="Curso" required>
                    <button type="button" onclick="this.parentNode.remove()" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
                `;
                document.getElementById('alunos-wrapper').appendChild(div);
                alunoCount++;
            }
        });

        document.getElementById('add-atividade')?.addEventListener('click', () => {
            const div = document.createElement('div');
            div.classList.add('mb-4');
            div.innerHTML = `
                <h4 class="font-semibold mb-2">Atividade ${atividadeCount + 1}</h4>
                <label class="block mb-1">O que fazer</label>
                <textarea name="atividades[${atividadeCount}][o_que_fazer]" class="form-control mb-2" placeholder="O que fazer?" required></textarea>
                <label class="block mb-1">Como fazer</label>
                <textarea name="atividades[${atividadeCount}][como_fazer]" class="form-control mb-2" placeholder="Como fazer?" required></textarea>
                <label class="block mb-1">Carga horária (horas)</label>
                <input type="number" name="atividades[${atividadeCount}][carga_horaria]" class="form-control mb-2" placeholder="Carga horária" required>
                <button type="button" onclick="this.parentNode.remove()" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
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
                <button type="button" onclick="this.parentNode.remove()" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
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
