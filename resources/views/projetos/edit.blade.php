<!-- Página de edição do projeto de extensão  -->

<x-app-layout>
<!-- Cabeçalho da página -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Projeto de Extensão') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
        <x-slot name="pageTitle">
            Editar Projeto de Extensão
        </x-slot>
        <!-- Definição de variáveis de permissão conforme papel do usuário -->
        @php
            $userRole = auth()->user()->role;
            $disableAlunoFields = in_array($userRole, ['coordenador', 'napex']);
            $disableNapexFields = in_array($userRole, ['coordenador', 'aluno']);
            $disableCoordenadorFields = !($userRole === 'coordenador');
        @endphp

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
                <input type="text" name="titulo" value="{{ old('titulo', $projeto->titulo) }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}" 
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="255"  required>

                <!-- Campo: Período  -->
                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" value="{{ old('periodo', $projeto->periodo) }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="50"  required>
                    
                    <!-- Campo: Professor(es) envolvidos -->
                    <label class="block mb-2">Professor(es) envolvidos:</label>
                    <div id="professores-wrapper">
                        @php
                            $professoresVelhos = old('professores', $projeto->professores->map(function($p) {
                                return ['id' => $p->user_id ?? $p->id, 'area' => $p->area];
                            })->toArray());
                        @endphp

                        @foreach ($professoresVelhos as $index => $prof)
                            @php
                                $selectedId = is_array($prof) ? ($prof['id'] ?? null) : ($prof->user_id ?? $prof->id ?? null);
                                $area = is_array($prof) ? ($prof['area'] ?? '') : ($prof->area ?? '');
                            @endphp
                            <div class="mb-4 flex items-center gap-4">
                                <select name="professores[{{ $index }}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                                    <option value="">-- Selecione um professor --</option>
                                    @foreach ($professores as $professor)
                                        <option value="{{ $professor->id }}" {{ $selectedId == $professor->id ? 'selected' : '' }}>
                                            {{ $professor->name }} ({{ $professor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="professores[{{ $index }}][area]" maxlength="100" value="{{ $area }}"
                                    class="w-full border-gray-300 rounded-md mb-2" placeholder="Área (opcional)">
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
                    @php
                        $alunosVelhos = old('alunos', $projeto->alunos->toArray());
                    @endphp

                    @foreach ($alunosVelhos as $index => $aluno)
                        @php
                            $nome = is_array($aluno) ? ($aluno['nome'] ?? '') : ($aluno->nome ?? '');
                            $ra = is_array($aluno) ? ($aluno['ra'] ?? '') : ($aluno->ra ?? '');
                            $curso = is_array($aluno) ? ($aluno['curso'] ?? '') : ($aluno->curso ?? '');
                        @endphp
                        <div class="mb-4">
                            <h4 class="font-semibold mb-2">Aluno {{ $index + 1 }}</h4>
                            <input type="text" name="alunos[{{ $index }}][nome]" value="{{ $nome }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="100" required>
                            <input type="text" name="alunos[{{ $index }}][ra]" value="{{ $ra }}"
                                class="w-full border-gray-300 rounded-md mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="50" required>
                            <input type="text" name="alunos[{{ $index }}][curso]" value="{{ $curso }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="100" required>
                        </div>
                    @endforeach

                </div>

                <!-- Botão para adicionar aluno (se permitido) -->
                @if(!$disableAlunoFields)
                    <button type="button" id="add-aluno" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Aluno</button>
                @endif

                <!-- Campo: Público Alvo -->
                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" maxlength="100" class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ old('publico_alvo', $projeto->publico_alvo) }} </textarea>

                <!-- Campos: Data     -->
                <label class="block mb-2">Data de Início:</label>
                <input type="date" name="data_inicio" value="{{ old('data_inicio', \Carbon\Carbon::parse($projeto->data_inicio)->format('Y-m-d')) }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                <label class="block mb-2">Data de Término:</label>
                <input type="date" name="data_fim" value="{{ old('data_fim', \Carbon\Carbon::parse($projeto->data_fim)->format('Y-m-d')) }}"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Detalhes do Projeto</legend>

                <!-- Campo: Introdução -->
                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="1000">{{ old('introducao', $projeto->introducao) }}</textarea>

                <!-- Campo: Objetivos do Projeto -->
                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="1000">{{ old('objetivo_geral', $projeto->objetivo_geral) }}</textarea>

                <!-- Campo: Justificativa -->
                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="1000">{{ old('justificativa', $projeto->justificativa) }}</textarea>

                <!-- Campo: Metodologia -->
                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="500">{{ old('metodologia', $projeto->metodologia) }}</textarea>

                <!-- Campo: Atividades a serem desenvolvidas -->
                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <div id="atividades-wrapper">
                    @php
                        $atividadesVelhas = old('atividades', $projeto->atividades->toArray());
                    @endphp

                    @foreach ($atividadesVelhas as $index => $atividade)
                        @php
                            $oque = is_array($atividade) ? ($atividade['o_que_fazer'] ?? '') : ($atividade->o_que_fazer ?? '');
                            $como = is_array($atividade) ? ($atividade['como_fazer'] ?? '') : ($atividade->como_fazer ?? '');
                            $carga = is_array($atividade) ? ($atividade['carga_horaria'] ?? '') : ($atividade->carga_horaria ?? '');
                        @endphp
                        <div class="mb-4">
                            <h4 class="font-semibold mb-2">Atividade {{ $index + 1 }}</h4>
                            <label class="block mb-1">O que fazer</label>
                            <textarea maxlength="1000" name="atividades[{{ $index }}][o_que_fazer]"
                                class="form-control mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>{{ $oque }} </textarea>
                            <label class="block mb-1">Como fazer</label>
                            <textarea maxlength="1000" name="atividades[{{ $index }}][como_fazer]"
                                class="form-control mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>{{ $como }}</textarea>
                            <label min=1 max=99999 class="block mb-1">Carga horária</label>
                            <input type="number" name="atividades[{{ $index }}][carga_horaria]" value="{{ $carga }}"
                                class="form-control mb-2 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>
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
                    @php
                        $cronogramaVelho = old('cronograma', $projeto->cronogramas->toArray());
                    @endphp

                    @foreach ($cronogramaVelho as $index => $cronograma)
                        @php
                            $atividade = is_array($cronograma) ? ($cronograma['atividade'] ?? '') : ($cronograma->atividade ?? '');
                            $mesSelecionado = is_array($cronograma) ? ($cronograma['mes'] ?? '') : ($cronograma->mes ?? '');
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <input type="text" name="cronograma[{{ $index }}][atividade]" maxlength="100" value="{{ $atividade }}"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'readonly disabled' : '' }} required>

                            <select name="cronograma[{{ $index }}][mes]"
                                class="w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                {{ $disableAlunoFields ? 'disabled' : '' }} required>
                                <option value="">Selecione o mês</option>
                                @foreach (['Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro'] as $mes)
                                    <option value="{{ $mes }}" {{ $mesSelecionado === $mes ? 'selected' : '' }}>{{ $mes }}</option>
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
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }}>{{ old('recursos', $projeto->recursos) }}</textarea>

                <!-- Campo: Resultados Esperados -->
                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="1000">{{ old('resultados_esperados', $projeto->resultados_esperados) }}</textarea>

            </fieldset>



            <div class="flex justify-center gap-4 mb-8">
                
                <!-- Botão Voltar -->
                <a href="{{ request('origem') === 'show' ? route('projetos.show', $projeto->id) : route('projetos.index') }}"
                class="bg-gray-600 flex hover:bg-gray-700 text-white font-bold gap-2 py-2 px-6 rounded">
                    <img src="{{ asset('img/site/btn-voltar.png') }}" alt="Voltar" width="20" height="20">
                    Voltar
                </a>

                
                <!-- Atualizar Projeto -->
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white gap-2  flex font-bold py-2 px-6 rounded">
                    <img src="{{ asset('img/site/btn-atualizar.png') }}" alt="Enviar projeto" width="20" height="20">
                     Atualizar Projeto
                </button>
            </div>
        </form>
                <!-- Botões de enviar projeto -->
                @if ($userRole === 'aluno')
                    @if ($projeto->status === 'editando')
                    <form method="POST" action="{{ route('projetos.enviar', $projeto->id) }}" class="mb-4">
                        @csrf
                        <div class="flex justify-center gap-4 mb-8">
                            <form method="POST" action="{{ route('projetos.enviar', $projeto->id) }}">
                                @csrf
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded flex items-center gap-2">
                                    <img src="{{ asset('img/site/btn-enviar.png') }}" alt="Enviar projeto" width="20" height="20">
                                    Enviar Projeto
                                </button>
                            </form>
                        </div>
                    </form>
                    @endif
                @endif
    </div>

    <script>
    const professorOptions = `
        <option value="">-- Selecione um professor --</option>
        ${Array.from(document.querySelector('select[name^="professores["][name$="[id]"]')?.options || [])
            .slice(1)
            .map(option => `<option value="${option.value}">${option.text}</option>`)
            .join('')}
    `;

    function atualizarTitulos(wrapperId, prefixo) {
        const items = document.querySelectorAll(`#${wrapperId} > div`);
        items.forEach((div, i) => {
            const title = div.querySelector('h4');
            if (title && prefixo) {
                title.textContent = `${prefixo} ${i + 1}`;
            }
        });
    }

    document.getElementById('add-professor')?.addEventListener('click', () => {
        const professorCount = document.querySelectorAll('#professores-wrapper > div').length;
        if (professorCount < 9) {
            const div = document.createElement('div');
            div.classList.add('mb-4');
            div.innerHTML = `
                <h4 class="font-semibold mb-2">Professor ${professorCount + 1}</h4>
                <select name="professores[${professorCount}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                    ${professorOptions}
                </select>
                <input type="text" name="professores[${professorCount}][area]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Área (opcional)">
                <button type="button" onclick="this.parentNode.remove(); atualizarTitulos('professores-wrapper', 'Professor');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
            `;
            document.getElementById('professores-wrapper').appendChild(div);
            atualizarTitulos('professores-wrapper', 'Professor');
        }
    });

    document.getElementById('add-aluno')?.addEventListener('click', () => {
        const alunoCount = document.querySelectorAll('#alunos-wrapper > div').length;
        if (alunoCount < 9) {
            const div = document.createElement('div');
            div.classList.add('mb-4');
            div.innerHTML = `
                <h4 class="font-semibold mb-2">Aluno ${alunoCount + 1}</h4>
                <input type="text" name="alunos[${alunoCount}][nome]" class="form-control mb-2" placeholder="Nome do aluno" required>
                <input type="text" name="alunos[${alunoCount}][ra]" class="form-control mb-2" placeholder="RA" required>
                <input type="text" name="alunos[${alunoCount}][curso]" class="form-control mb-2" placeholder="Curso" required>
                <button type="button" onclick="this.parentNode.remove(); atualizarTitulos('alunos-wrapper', 'Aluno');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
            `;
            document.getElementById('alunos-wrapper').appendChild(div);
            atualizarTitulos('alunos-wrapper', 'Aluno');
        }
    });

    document.getElementById('add-atividade')?.addEventListener('click', () => {
        const atividadeCount = document.querySelectorAll('#atividades-wrapper > div').length;
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
            <button type="button" onclick="this.parentNode.remove(); atualizarTitulos('atividades-wrapper', 'Atividade');" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('atividades-wrapper').appendChild(div);
        atualizarTitulos('atividades-wrapper', 'Atividade');
    });

    document.getElementById('add-cronograma')?.addEventListener('click', () => {
        const cronogramaCount = document.querySelectorAll('#cronograma-wrapper > div').length;
        const div = document.createElement('div');
        div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mb-4');
        div.innerHTML = `
            <input type="text" name="cronograma[${cronogramaCount}][atividade]" class="form-control" placeholder="Título da Atividade" required>
            <select name="cronograma[${cronogramaCount}][mes]" class="form-control" required>
                <option value="">Selecione o mês</option>
                ${['Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro'].map(m => `<option value="${m}">${m}</option>`).join('')}
            </select>
            <button type="button" onclick="this.parentNode.remove();" class="bg-red-600 hover:bg-red-700 text-white py-1 px-2 rounded">Remover</button>
        `;
        document.getElementById('cronograma-wrapper').appendChild(div);
    });


</script>


</x-app-layout>
