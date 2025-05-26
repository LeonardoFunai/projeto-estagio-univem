<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cadastrar Projeto de Extensão') }}
        </h2>
    </x-slot>
    

    <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
        <x-slot name="pageTitle">
            Cadastrar Projeto de Extensão
        </x-slot>
        

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

        
        <form id="form-projeto" action="{{ route('projetos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <fieldset class="mb-8">
                <!-- Trilha de Status -->
                <div class="flex items-end justify-center space-x-6 mt-3">

                    {{-- Etapas principais reduzidas --}}
                    <div class="flex space-x-6 self-center">
                        @foreach ([
                            ['label' => 'Proposta Criada', 'classe' => 'atual'],
                            ['label' => 'Editando', 'classe' => 'futuro'],
                            ['label' => 'Entregue', 'classe' => 'futuro'],
                        ] as $i => $etapa)
                            <div class="flex flex-col items-center">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center 
                                    @if($etapa['classe'] === 'atual')
                                        bg-blue-600 text-white border-blue-800 shadow animate-pulse
                                    @else
                                        bg-gray-300 text-gray-600 border-gray-400 shadow-sm
                                    @endif text-xs font-bold">
                                    {{ $i + 1 }}
                                </div>
                                <span class="mt-1 text-xs text-center">{{ $etapa['label'] }}</span>
                            </div>

                            @if ($i < 2)
                                <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 my-auto"></div>
                            @endif
                        @endforeach
                    </div>

                    {{-- seta para aprovações --}}
                    <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 self-center"></div>

                    {{-- Aprovações empilhadas compactas --}}
                    <div class="flex flex-col justify-between space-y-4 items-center mt-[-20px]">
                        <div class="flex flex-col items-center">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm text-xs font-bold">N</div>
                            <span class="mt-1 text-xs text-center">NAPEx</span>
                        </div>

                        <div class="flex flex-col items-center">
                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm text-xs font-bold">C</div>
                            <span class="mt-1 text-xs text-center">Coordenação</span>
                        </div>
                    </div>

                    {{-- seta final --}}
                    <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 self-center"></div>

                    {{-- Aprovado Final compacto --}}
                    <div class="flex flex-col self-center items-center">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm text-xs font-bold">
                            ✓
                        </div>
                        <span class="mt-1 text-xs font-medium text-center text-gray-400">Aprovado</span>
                    </div>
                </div>


                <legend class="text-lg font-semibold text-blue-700 mb-4">Introdução</legend>

                <label class="block mb-2">Título do Projeto:</label>
                <input type="text"  name="titulo" class="w-full border-gray-300 rounded-md mb-4" placeholder="Título do Projeto" value="{{ old('titulo') }}" maxlength="255" required>

                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" class="w-full border-gray-300 rounded-md mb-4" placeholder="Fevereiro a Junho de 2025." value="{{ old('periodo') }}" maxlength="50" required>
                
                <label class="block mb-2">Selecione o Professor Responsável:</label>
                <p><strong>Professor 1</strong></p>
                <div id="professores-wrapper">
                    <div class="mb-4">
                        <select name="professores[0][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                            <option value="">-- Selecione um professor --</option>
                            @foreach($professores as $professor)
                                <option value="{{ $professor->id }}">{{ $professor->name }} ({{ $professor->email }})</option>
                            @endforeach
                        </select>
                        <input type="text" name="professores[0][area]" class="w-full border-gray-300 rounded-md" value="{{ old('professores.0.area') }}" maxlength="100" placeholder="Área (opcional) ">
                    </div>
                </div>  

            <button type="button" id="add-professor" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Professor</button>


                <label class="block mb-2">Alunos envolvidos / R.A / Curso:</label>
                <p><strong>Aluno 1</strong></p>
                <div id="alunos-wrapper">
                    <div class="mb-4">
                        <input type="text" name="alunos[0][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do aluno" value="{{ old('alunos.0.nome') }}" maxlength="100" required>
                        <input type="text" name="alunos[0][ra]" class="w-full border-gray-300 rounded-md mb-2" placeholder="RA" value="{{ old('alunos.0.ra') }}" maxlength="50" required>
                        <input type="text" name="alunos[0][curso]" class="w-full border-gray-300 rounded-md" placeholder="Curso" value="{{ old('alunos.0.curso') }}" maxlength="100" required>
                    </div>
                </div>
                <button type="button" id="add-aluno" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Aluno</button>

                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-1" placeholder="População em Geral" maxlength="100">{{ old('publico_alvo') }} </textarea>

                <label class="block mb-2">Data de Início:</label>
                <input type="date" name="data_inicio" id="data_inicio" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_inicio') }}" required>

                <label class="block mb-2">Data de Término:</label>
                <input type="date" name="data_fim" id="data_fim" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_fim') }}" required>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Detalhes do Projeto</legend>

                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('introducao') }}</textarea>

                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('objetivo_geral') }}</textarea>


                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('justificativa') }}</textarea>


                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia" class="w-full border-gray-300 rounded-md mb-4" maxlength="500">{{ old('metodologia') }}</textarea>


                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <small class="block mb-2 text-gray-600">(O que fazer, como fazer e carga horária)</small>
                <p><strong>Atividade 1</strong></p>

                <div id="atividades-wrapper">
                    <div class="mb-4">
                        <textarea name="atividades[0][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" maxlength="1000" required>{{ old('atividades.0.o_que_fazer') }}</textarea>
                        <textarea name="atividades[0][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" maxlength="1000" required>{{ old('atividades.0.como_fazer') }}</textarea>
                        <input type="number" name="atividades[0][carga_horaria]" class="w-full border-gray-300 rounded-md" min=1 max=99999 placeholder="Carga horária" value="{{ old('atividades.0.carga_horaria') }}" required>
                    </div>
                </div>
                <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade</button>

                <label class="block mb-2 text-lg font-semibold text-blue-700">Cronograma</label>
                <div id="cronograma-wrapper">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <input type="text" name="cronograma[0][atividade]" class="form-control" maxlength="100" placeholder="Título da Atividade" value="{{ old('cronograma.0.atividade') }}" required>
                        <select name="cronograma[0][mes]" class="form-control" maxlength="20" required>
                            <option value="">Selecione o mês</option>
                            @foreach(['Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro'] as $mes)
                                <option value="{{ $mes }}">{{ $mes }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Cronograma</button>

                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('recursos') }}</textarea>

                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('resultados_esperados') }}</textarea>

            </fieldset>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Salvar Projeto</button>
        </form>
    </div>

<script>
    let professorCount = 1;
    let alunoCount = 1;
    let atividadeCount = 1;
    let cronogramaCount = 1;

    const professorOptions = `
        <option value="">-- Selecione um professor --</option>
        ${Array.from(document.querySelector('select[name="professores[0][id]"]').options)
            .slice(1)
            .map(option => `<option value="${option.value}">${option.text}</option>`)
            .join('')}
    `;

    function reindexarCampos(wrapperId, prefixo, nameBase) {
        const divs = document.querySelectorAll(`#${wrapperId} > div`);
        divs.forEach((div, i) => {
            const h4 = div.querySelector('h4');
            if (h4) h4.textContent = `${prefixo} ${i + 1}`;

            const inputs = div.querySelectorAll('[name]');
            inputs.forEach(input => {
                const nameAttr = input.getAttribute('name');
                const matches = nameAttr.match(/\[\d+]\[(\w+)]/);
                if (matches && matches[1]) {
                    input.setAttribute('name', `${nameBase}[${i}][${matches[1]}]`);
                }
            });
        });
    }

    document.getElementById('add-professor')?.addEventListener('click', () => {
        const index = professorCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Professor</h4>
            <select name="professores[${index}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                ${professorOptions}
            </select>
            <input type="text" name="professores[${index}][area]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Área (opcional) maxlength="100"">
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('professores-wrapper', 'Professor', 'professores');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('professores-wrapper').appendChild(div);
        reindexarCampos('professores-wrapper', 'Professor', 'professores');
    });

    document.getElementById('add-aluno')?.addEventListener('click', () => {
        const index = alunoCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Aluno</h4>
            <input type="text" name="alunos[${index}][nome]" class="form-control mb-2" placeholder="Nome do aluno" maxlength="100" required>
            <input type="text" name="alunos[${index}][ra]" class="form-control mb-2" placeholder="RA" maxlength="50" required>
            <input type="text" name="alunos[${index}][curso]" class="form-control mb-2" placeholder="Curso" maxlength="100" required>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('alunos-wrapper').appendChild(div);
        reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');
    });

    document.getElementById('add-atividade')?.addEventListener('click', () => {
        const index = atividadeCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Atividade</h4>
            <label class="block mb-1">O que fazer</label>
            <textarea name="atividades[${index}][o_que_fazer]" class="form-control mb-2" required></textarea>
            <label class="block mb-1">Como fazer</label>
            <textarea name="atividades[${index}][como_fazer]" class="form-control mb-2" required></textarea>
            <label class="block mb-1">Carga horária</label>
            <input type="number" name="atividades[${index}][carga_horaria]" class="form-control mb-2" maxlength="100" required>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('atividades-wrapper').appendChild(div);
        reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');
    });

    document.getElementById('add-cronograma')?.addEventListener('click', () => {
        const index = cronogramaCount++;
        const div = document.createElement('div');
        div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mb-4');
        div.innerHTML = `
            <input type="text" name="cronograma[${index}][atividade]" class="form-control" placeholder="Título da Atividade" maxlength="100" required>
            <select name="cronograma[${index}][mes]" class="form-control" required>
                <option value="">Selecione o mês</option>
                ${['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}">${m}</option>`).join('')}
            </select>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('cronograma-wrapper', '', 'cronograma');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('cronograma-wrapper').appendChild(div);
        reindexarCampos('cronograma-wrapper', '', 'cronograma');
    });

    document.getElementById('form-projeto').addEventListener('submit', function (e) {
        const inicio = document.getElementById('data_inicio').value;
        const fim = document.getElementById('data_fim').value;
        if (!inicio || !fim || new Date(inicio) > new Date(fim)) {
            e.preventDefault();
            alert('A data de início deve ser anterior ou igual à data de fim.');
        }
    });
        const oldProfessores = @json(old('professores', []));
    const oldAlunos = @json(old('alunos', []));
    const oldAtividades = @json(old('atividades', []));
    const oldCronograma = @json(old('cronograma', []));

    // Recria Professores
    oldProfessores.slice(1).forEach((professor, i) => {
        const index = professorCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Professor ${index + 1}</h4>
            <select name="professores[${index}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                ${professorOptions}
            </select>
            <input type="text" name="professores[${index}][area]" class="w-full border-gray-300 rounded-md mb-2" value="${professor.area ?? ''}" placeholder="Área (opcional) maxlength="100" ">
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('professores-wrapper', 'Professor', 'professores');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('professores-wrapper').appendChild(div);
        div.querySelector('select').value = professor.id;
    });

    // Recria Alunos
    oldAlunos.slice(1).forEach((aluno, i) => {
        const index = alunoCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Aluno ${index + 1}</h4>
            <input type="text" name="alunos[${index}][nome]" class="form-control mb-2" value="${aluno.nome ?? ''}" placeholder="Nome do aluno" maxlength="100" required>
            <input type="text" name="alunos[${index}][ra]" class="form-control mb-2" value="${aluno.ra ?? ''}" placeholder="RA" maxlength="50" required>
            <input type="text" name="alunos[${index}][curso]" class="form-control mb-2" value="${aluno.curso ?? ''}" placeholder="Curso" maxlength="100" required>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('alunos-wrapper').appendChild(div);
    });

    // Recria Atividades
    oldAtividades.slice(1).forEach((atividade, i) => {
        const index = atividadeCount++;
        const div = document.createElement('div');
        div.classList.add('mb-4');
        div.innerHTML = `
            <h4 class="font-semibold mb-2">Atividade ${index + 1}</h4>
            <label class="block mb-1">O que fazer</label>
            <textarea name="atividades[${index}][o_que_fazer]" class="form-control mb-2" required>${atividade.o_que_fazer ?? ''}</textarea>
            <label class="block mb-1">Como fazer</label>
            <textarea name="atividades[${index}][como_fazer]" class="form-control mb-2" required>${atividade.como_fazer ?? ''}</textarea>
            <label class="block mb-1">Carga horária</label>
            <input type="number" name="atividades[${index}][carga_horaria]" class="form-control mb-2" value="${atividade.carga_horaria ?? ''}" required>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('atividades-wrapper').appendChild(div);
    });

    // Recria Cronogramas
    oldCronograma.slice(1).forEach((item, i) => {
        const index = cronogramaCount++;
        const div = document.createElement('div');
        div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mb-4');
        div.innerHTML = `
            <input type="text" name="cronograma[${index}][atividade]" class="form-control" value="${item.atividade ?? ''}" placeholder="Título da Atividade" maxlength="100" required>
            <select name="cronograma[${index}][mes]" class="form-control" required>
                <option value="">Selecione o mês</option>
                ${['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}" ${item.mes === m ? 'selected' : ''}>${m}</option>`).join('')}
            </select>
            <button type="button" onclick="this.parentNode.remove(); reindexarCampos('cronograma-wrapper', '', 'cronograma');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('cronograma-wrapper').appendChild(div);
    });
</script>








</x-app-layout>
