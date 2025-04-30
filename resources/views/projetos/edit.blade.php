<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Projeto de Extensão') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-blue-800 mb-8">Editar Projeto de Extensão</h1>

        @php
            $userRole = auth()->user()->role;
            $disableAlunoFields = in_array($userRole, ['coordenador', 'napex']);
            $disableNapexFields = in_array($userRole, ['coordenador', 'aluno']);
            $disableCoordenadorFields = !($userRole === 'coordenador');
        @endphp

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


        <form id="form-projeto" action="{{ route('projetos.update', $projeto->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Introdução</legend>


                <label class="block mb-2">Título do Projeto:</label>
                <input type="text" name="titulo" value="{{ $projeto->titulo }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}" 
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" value="{{ $projeto->periodo }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                <label class="block mb-2">Professor(es) envolvidos:</label>
                <div id="professores-wrapper">
                    @foreach ($projeto->professores as $index => $prof)
                        <div class="mb-4">
                            <input type="text" name="professores[{{ $index }}][nome]" value="{{ $prof->nome }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
                            <input type="email" name="professores[{{ $index }}][email]" value="{{ $prof->email }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>
                            <input type="text" name="professores[{{ $index }}][area]" value="{{ $prof->area }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }}>
                        </div>
                    @endforeach
                </div>

                @if(!$disableAlunoFields)
                    <button type="button" id="add-professor" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Professor</button>
                @endif

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

                @if(!$disableAlunoFields)
                    <button type="button" id="add-aluno" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Aluno</button>
                @endif

                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->publico_alvo }}</textarea>

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

                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->introducao }}</textarea>

                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->objetivo_geral }}</textarea>

                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->justificativa }}</textarea>

                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->metodologia }}</textarea>

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

                @if(!$disableAlunoFields)
                    <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                        + Adicionar Atividade
                    </button>
                @endif

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

                @if(!$disableAlunoFields)
                    <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                        + Adicionar Cronograma
                    </button>
                @endif

                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->recursos }}</textarea>

                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ $projeto->resultados_esperados }}</textarea>

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
        let professorCount = {{ $projeto->professores->count() }};
        let alunoCount = {{ $projeto->alunos->count() }};
        let atividadeCount = {{ $projeto->atividades->count() }};
        let cronogramaCount = {{ $projeto->cronogramas ? $projeto->cronogramas->count() : 0 }};

        let professoresWrapper = document.getElementById('professores-wrapper');
        let alunosWrapper = document.getElementById('alunos-wrapper');
        let atividadesWrapper = document.getElementById('atividades-wrapper');
        let cronogramaWrapper = document.getElementById('cronograma-wrapper');

        @if(!$disableAlunoFields)
        document.getElementById('add-professor')?.addEventListener('click', function () {
            if (professorCount < 9) {
                const div = document.createElement('div');
                div.classList.add('mb-4');
                div.innerHTML = `
                    <input type="text" name="professores[${professorCount}][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do professor" required>
                    <input type="email" name="professores[${professorCount}][email]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Email (opcional)">
                    <input type="text" name="professores[${professorCount}][area]" class="w-full border-gray-300 rounded-md" placeholder="Área (opcional)">
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                professoresWrapper.appendChild(div);
                professorCount++;
            }
        });

        document.getElementById('add-aluno')?.addEventListener('click', function () {
            if (alunoCount < 9) {
                const div = document.createElement('div');
                div.classList.add('mb-4');
                div.innerHTML = `
                    <input type="text" name="alunos[${alunoCount}][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do aluno" required>
                    <input type="text" name="alunos[${alunoCount}][ra]" class="w-full border-gray-300 rounded-md mb-2" placeholder="RA" required>
                    <input type="text" name="alunos[${alunoCount}][curso]" class="w-full border-gray-300 rounded-md" placeholder="Curso" required>
                    <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
                `;
                alunosWrapper.appendChild(div);
                alunoCount++;
            }
        });

        document.getElementById('add-atividade')?.addEventListener('click', function () {
            const div = document.createElement('div');
            div.classList.add('mb-4');
            div.innerHTML = `
                <textarea name="atividades[${atividadeCount}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required></textarea>
                <textarea name="atividades[${atividadeCount}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required></textarea>
                <input type="number" name="atividades[${atividadeCount}][carga_horaria]" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required>
                <button type="button" onclick="this.parentNode.remove()" class="btn btn-danger mb-2">Remover</button>
            `;
            atividadesWrapper.appendChild(div);
            atividadeCount++;
        });

        document.getElementById('add-cronograma')?.addEventListener('click', function () {
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
        @endif

        document.getElementById('form-projeto').addEventListener('submit', function (e) {
            const inicio = document.getElementById('data_inicio')?.value;
            const fim = document.getElementById('data_fim')?.value;

            if (inicio && fim && new Date(inicio) > new Date(fim)) {
                e.preventDefault();
                alert('A data de início deve ser anterior ou igual à data de término.');
            }
        });
    </script>

</x-app-layout>
