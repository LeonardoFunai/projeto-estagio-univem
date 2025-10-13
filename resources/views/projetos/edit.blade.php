<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Projeto de Extensão') }}
        </h2>
    </x-slot>

    <div class="max-w-9xl mx-auto mt-8 p-8 bg-white rounded-lg">
        <x-slot name="pageTitle">
            Editar Projeto de Extensão
        </x-slot>

        {{-- Barra de Progresso --}}
        <div class="flex items-end justify-center space-x-6 mt-3 mb-8">
            <div class="flex space-x-6 self-center">
                 @foreach ([
                    ['label' => 'Proposta Criada', 'classe' => 'concluida'],
                    ['label' => 'Editando', 'classe' => 'atual'],
                    ['label' => 'Entregue', 'classe' => 'futuro'],
                ] as $i => $etapa)
                    <div class="flex flex-col items-center">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center
                            @if($etapa['classe'] === 'concluida') bg-green-500 text-white border-green-600 shadow
                            @elseif($etapa['classe'] === 'atual') bg-blue-600 text-white border-blue-800 shadow animate-pulse
                            @else bg-gray-300 text-gray-600 border-gray-400 shadow-sm @endif text-xs font-bold">
                            {{ $i + 1 }}
                        </div>
                        <span class="mt-1 text-xs text-center">{{ $etapa['label'] }}</span>
                    </div>
                    @if ($i < 2)
                        <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 my-auto"></div>
                    @endif
                @endforeach
            </div>
            <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 self-center"></div>
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
            <div class="w-6 h-0.5 bg-gray-300 shadow-md skew-x-12 self-center"></div>
            <div class="flex flex-col self-center items-center">
                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center bg-gray-300 text-gray-600 border-gray-400 shadow-sm text-xs font-bold">✓</div>
                <span class="mt-1 text-xs font-medium text-center text-gray-400">Aprovado</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-projeto" action="{{ route('projetos.update', $projeto->id) }}" method="POST">
            @csrf
            @method('PUT')

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Informações Básicas</legend>
                <label class="block mb-2">Título do Projeto:</label>
                <input type="text" name="titulo" value="{{ old('titulo', $projeto->titulo) }}" class="w-full border-gray-300 rounded-md mb-4" required>
                <label class="block mb-2">Período:</label>
                <input type="text" name="periodo" value="{{ old('periodo', $projeto->periodo) }}" class="w-full border-gray-300 rounded-md mb-4" required>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Professores Orientadores</legend>
                <div id="professores-wrapper">
                    @php
                        $professoresDoProjeto = $projeto->users->filter(fn($u) => str_starts_with($u->role, 'professor') || str_starts_with($u->role, 'coordenador'));
                    @endphp
                    @foreach (old('professores', $professoresDoProjeto->pluck('id')->all()) as $professorId)
                        @php $p = $professores->find($professorId); @endphp
                        @if ($p)
                        <div class="search-component mb-4 p-4 border rounded-md relative">
                            <div class="flex justify-between items-center mb-2">
                                <p><strong>Professor {{ $loop->iteration }}</strong></p>
                                <button type="button" class="remove-btn bg-red-600 text-white text-xs px-2 py-1 rounded">Remover</button>
                            </div>
                            <div class="search-container" style="display: none;">
                                <input type="text" class="search-input w-full border-gray-300 rounded-md" placeholder="Buscar por nome ou email...">
                                <ul class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10" style="width: calc(100% - 2rem);"></ul>
                            </div>
                            <div class="selected-user mt-2 text-sm text-gray-800 space-y-1">
                                <p><strong>Nome:</strong> {{ $p->name }}</p>
                                <p><strong>Email:</strong> {{ $p->email }}</p>
                                <button type="button" class="change-btn text-blue-600 underline text-xs mt-1">Trocar</button>
                            </div>
                            <input type="hidden" name="professores[]" class="user-id-input" value="{{ $p->id }}">
                        </div>
                        @endif
                    @endforeach
                </div>
                <button type="button" id="add-professor-search" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">+ Adicionar Orientador</button>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Alunos Participantes</legend>
                <div class="mb-4 p-4 border rounded-md bg-gray-50">
                    <p><strong>Proponente (Aluno 1):</strong></p>
                    <p><strong>Nome:</strong> {{ $projeto->user->name }}</p>
                    <p><strong>RA:</strong> {{ $projeto->user->ra ?? 'N/A' }}</p>
                    <p><strong>Curso:</strong> {{ $projeto->user->curso->nome ?? 'N/A' }}</p>
                </div>
                <div id="alunos-wrapper">
                    @php
                        $alunosDoProjeto = $projeto->users->where('role', 'aluno')->where('id', '!=', $projeto->user_id);
                    @endphp
                    @foreach (old('alunos', $alunosDoProjeto->pluck('id')->all()) as $alunoId)
                        @php $a = $alunos->find($alunoId); @endphp
                        @if ($a)
                        <div class="search-component mb-4 p-4 border rounded-md relative">
                            <div class="flex justify-between items-center mb-2">
                                <p><strong>Aluno {{ $loop->iteration + 1 }}</strong></p>
                                <button type="button" class="remove-btn bg-red-600 text-white text-xs px-2 py-1 rounded">Remover</button>
                            </div>
                             <div class="search-container" style="display: none;">
                                <input type="text" class="search-input w-full border-gray-300 rounded-md" placeholder="Buscar por nome, RA ou curso...">
                                <ul class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10" style="width: calc(100% - 2rem);"></ul>
                            </div>
                            <div class="selected-user mt-2 text-sm text-gray-800 space-y-1">
                                <p><strong>Nome:</strong> {{ $a->name }}</p>
                                <p><strong>RA:</strong> {{ $a->ra ?? 'N/A' }}</p>
                                <p><strong>Curso:</strong> {{ $a->curso->nome ?? 'N/A' }}</p>
                                <button type="button" class="change-btn text-blue-600 underline text-xs mt-1">Trocar</button>
                            </div>
                            <input type="hidden" name="alunos[]" class="user-id-input" value="{{ $a->id }}">
                        </div>
                        @endif
                    @endforeach
                </div>
                @if ($role === 'aluno') 
                    <button type="button" id="add-aluno-search" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">+ Adicionar Outro Aluno</button>
                @endif
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Datas e Público</legend>
                <label class="block mb-2">Público Alvo:</label>
                <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-4">{{ old('publico_alvo', $projeto->publico_alvo) }}</textarea>
                <label class="block mb-2">Data de Início:</label>
                <input type="date" name="data_inicio" id="data_inicio" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_inicio', $projeto->data_inicio) }}" required>
                <label class="block mb-2">Data de Término:</label>
                <input type="date" name="data_fim" id="data_fim" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_fim', $projeto->data_fim) }}" required>
            </fieldset>

            <fieldset class="mb-8">
                <legend class="text-lg font-semibold text-blue-700 mb-4">Conteúdo do Projeto</legend>
                <label class="block mb-2">1. Introdução</label>
                <textarea name="introducao" class="w-full border-gray-300 rounded-md mb-4">{{ old('introducao', $projeto->introducao) }}</textarea>
                <label class="block mb-2">2. Objetivos do Projeto</label>
                <textarea name="objetivo_geral" class="w-full border-gray-300 rounded-md mb-4">{{ old('objetivo_geral', $projeto->objetivo_geral) }}</textarea>
                <label class="block mb-2">3. Justificativa</label>
                <textarea name="justificativa" class="w-full border-gray-300 rounded-md mb-4">{{ old('justificativa', $projeto->justificativa) }}</textarea>
                <label class="block mb-2">4. Metodologia</label>
                <textarea name="metodologia" class="w-full border-gray-300 rounded-md mb-4">{{ old('metodologia', $projeto->metodologia) }}</textarea>

                <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                <div id="atividades-wrapper">
                    @foreach (old('atividades', $projeto->atividades->toArray()) as $index => $atividade)
                        <div class="mb-4 border p-3 rounded-md dynamic-item">
                            <div class="flex justify-between items-center mb-2">
                                <strong>Atividade {{ $index + 1 }}</strong>
                                <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                            </div>
                            <textarea name="atividades[{{ $index }}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" required>{{ $atividade['o_que_fazer'] ?? '' }}</textarea>
                            <textarea name="atividades[{{ $index }}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" required>{{ $atividade['como_fazer'] ?? '' }}</textarea>
                            <input type="number" name="atividades[{{ $index }}][carga_horaria]" class="w-full border-gray-300 rounded-md" value="{{ $atividade['carga_horaria'] ?? '' }}" required min="1">
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade</button>

                <label class="block mb-2 text-lg font-semibold text-blue-700">6. Cronograma</label>
                <div id="cronograma-wrapper">
                     @foreach (old('cronograma', $projeto->cronogramas->toArray()) as $index => $item)
                        <div class="border p-4 rounded-md mb-4 dynamic-item cronograma-item">
                            <div class="flex justify-between items-center mb-2">
                                <strong>Atividade do Cronograma {{ $index + 1 }}</strong>
                                <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <input type="text" name="cronograma[{{ $index }}][atividade]" value="{{ $item['atividade'] ?? '' }}" class="form-input w-full border-gray-300 rounded-md" required>
                                <select name="cronograma[{{ $index }}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required>
                                    @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $mes)
                                        <option value="{{ $mes }}" {{ ($item['mes_inicio'] ?? '') == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                                    @endforeach
                                </select>
                                <select name="cronograma[{{ $index }}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required>
                                     @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $mes)
                                        <option value="{{ $mes }}" {{ ($item['mes_fim'] ?? '') == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade ao Cronograma</button>

                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4">{{ old('recursos', $projeto->recursos) }}</textarea>
                <label class="block mb-2">8. Resultados Esperados</label>
                <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4">{{ old('resultados_esperados', $projeto->resultados_esperados) }}</textarea>
            </fieldset>

            <div class="flex justify-center gap-4">
                <a href="{{ route('projetos.show', $projeto->id) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">Cancelar</a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Salvar Alterações</button>
            </div>
        </form>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const createSearchComponent = (type, pluralName, wrapperId, addButtonId, initialCount = 0) => {
            const wrapper = document.getElementById(wrapperId);
            const addButton = document.getElementById(addButtonId);
            let userCount = wrapper.querySelectorAll('.search-component').length + initialCount;

            const reindexTitles = () => {
                wrapper.querySelectorAll('.search-component').forEach((comp, i) => {
                    const titleElement = comp.querySelector('p > strong');
                    if (titleElement) {
                        const newTitle = `${type.charAt(0).toUpperCase() + type.slice(1)} ${i + 1 + initialCount}`;
                        titleElement.textContent = newTitle;
                    }
                });
                userCount = wrapper.querySelectorAll('.search-component').length + initialCount;
            };

            const addSearchField = () => {
                const title = `${type.charAt(0).toUpperCase() + type.slice(1)} ${userCount + 1}`;
                userCount++;
                
                const searchComponent = document.createElement('div');
                searchComponent.className = 'search-component mb-4 p-4 border rounded-md relative';
                
                searchComponent.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <p><strong>${title}</strong></p>
                        <button type="button" class="remove-btn bg-red-600 text-white text-xs px-2 py-1 rounded">Remover</button>
                    </div>
                    <div class="search-container">
                        <input type="text" class="search-input w-full border-gray-300 rounded-md" placeholder="Buscar...">
                        <ul class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10" style="width: calc(100% - 2rem);"></ul>
                    </div>
                    <div class="selected-user mt-2 text-sm text-gray-800 space-y-1" style="display: none;"></div>
                    <input type="hidden" name="${pluralName}[]" class="user-id-input">
                `;
                wrapper.appendChild(searchComponent);
            };

            addButton.addEventListener('click', addSearchField);

            wrapper.addEventListener('input', async (e) => {
                if (!e.target.classList.contains('search-input')) return;
                const component = e.target.closest('.search-component');
                const resultsList = component.querySelector('.search-results');
                const searchTerm = e.target.value;

                if (searchTerm.length < 3) {
                    resultsList.classList.add('hidden');
                    return;
                }

                const role = (type === 'aluno') ? 'aluno' : 'professor';
                const excludeId = {{ $projeto->user->id }};
                const response = await fetch(`{{ route('users.search') }}?search=${searchTerm}&role=${role}&exclude=${excludeId}`);
                const users = await response.json();

                resultsList.innerHTML = '';
                if (users.length > 0) {
                    users.forEach(user => {
                        const li = document.createElement('li');
                        li.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
                        const courseName = user.curso ? user.curso.nome : 'N/A';
                        li.textContent = type === 'aluno' ? `${user.name} (RA: ${user.ra || 'N/A'}, Curso: ${courseName})` : `${user.name} (${user.email})`;
                        li.dataset.id = user.id;
                        li.dataset.name = user.name;
                        li.dataset.ra = user.ra || '';
                        li.dataset.curso = courseName;
                        li.dataset.email = user.email || '';
                        resultsList.appendChild(li);
                    });
                } else {
                    resultsList.innerHTML = '<li class="p-2 text-gray-500">Nenhum usuário encontrado.</li>';
                }
                resultsList.classList.remove('hidden');
            });

            wrapper.addEventListener('click', (e) => {
                if (e.target.tagName === 'LI' && e.target.closest('.search-results')) {
                    const component = e.target.closest('.search-component');
                    const selectedDiv = component.querySelector('.selected-user');
                    const hiddenInput = component.querySelector('.user-id-input');
                    const searchContainer = component.querySelector('.search-container');
                    
                    hiddenInput.value = e.target.dataset.id;
                    
                    let selectedHTML = `<p><strong>Nome:</strong> ${e.target.dataset.name}</p>`;
                    if (type === 'aluno') {
                        selectedHTML += `<p><strong>RA:</strong> ${e.target.dataset.ra || 'N/A'}</p><p><strong>Curso:</strong> ${e.target.dataset.curso || 'N/A'}</p>`;
                    } else {
                         selectedHTML += `<p><strong>Email:</strong> ${e.target.dataset.email || 'N/A'}</p>`;
                    }
                    selectedHTML += `<button type="button" class="change-btn text-blue-600 underline text-xs mt-1">Trocar</button>`;
                    
                    selectedDiv.innerHTML = selectedHTML;
                    selectedDiv.style.display = 'block';
                    searchContainer.style.display = 'none';
                }

                if (e.target.classList.contains('remove-btn')) {
                    e.target.closest('.search-component').remove();
                    reindexTitles();
                }
                
                if (e.target.classList.contains('change-btn')) {
                    const component = e.target.closest('.search-component');
                    component.querySelector('.selected-user').style.display = 'none';
                    component.querySelector('.user-id-input').value = '';
                    const searchContainer = component.querySelector('.search-container');
                    searchContainer.style.display = 'block';
                    searchContainer.querySelector('.search-input').focus();
                }
            });
        };
        
        createSearchComponent('professor', 'professores', 'professores-wrapper', 'add-professor-search', 0);
        createSearchComponent('aluno', 'alunos', 'alunos-wrapper', 'add-aluno-search', 1);

        const setupDynamicFields = (type, wrapperId, addButtonId, templateFunction) => {
            const wrapper = document.getElementById(wrapperId);
            const addButton = document.getElementById(addButtonId);

            const reindexFields = () => {
                const items = wrapper.children;
                Array.from(items).forEach((item, index) => {
                    item.querySelector('strong').textContent = `${type} ${index + 1}`;
                    item.querySelectorAll('[name]').forEach(field => {
                        field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
                    });
                    const removeBtn = item.querySelector('.remove-item-btn');
                    if(removeBtn) {
                       removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                    }
                });
            };

            const addField = () => {
                const index = wrapper.children.length;
                const newField = document.createElement('div');
                newField.innerHTML = templateFunction(index); 
                wrapper.appendChild(newField.firstElementChild);
                reindexFields();
            };
            
            addButton.addEventListener('click', addField);

            wrapper.addEventListener('click', (e) => {
                if (e.target && e.target.classList.contains('remove-item-btn')) {
                    e.target.closest('.dynamic-item').remove();
                    reindexFields();
                }
            });
            
            if (wrapper.children.length === 0) {
                addField();
            } else {
                reindexFields();
            }
        };
        
        const atividadeTemplate = (index) => `
            <div class="mb-4 border p-3 rounded-md dynamic-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <textarea name="atividades[${index}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required></textarea>
                <textarea name="atividades[${index}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required></textarea>
                <input type="number" name="atividades[${index}][carga_horaria]" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required min="1">
            </div>`;
        
        const cronogramaTemplate = (index) => {
            const mesesOptionsHtml = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}">${m}</option>`).join('');
            return `
            <div class="border p-4 rounded-md mb-4 dynamic-item cronograma-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade do Cronograma ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <input type="text" name="cronograma[${index}][atividade]" class="form-input w-full border-gray-300 rounded-md" placeholder="Título da Atividade" required>
                    <select name="cronograma[${index}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Início --</option>${mesesOptionsHtml}</select>
                    <select name="cronograma[${index}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Fim --</option>${mesesOptionsHtml}</select>
                </div>
            </div>`;
        };

        setupDynamicFields('Atividade', 'atividades-wrapper', 'add-atividade', atividadeTemplate);
        setupDynamicFields('Atividade do Cronograma', 'cronograma-wrapper', 'add-cronograma', cronogramaTemplate);
    });
    </script>
</x-app-layout>