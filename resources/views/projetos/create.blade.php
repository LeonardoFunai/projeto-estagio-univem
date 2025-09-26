<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cadastrar Projeto de Extensão') }}
        </h2>
    </x-slot>
    
    @can('create', App\Models\Projeto::class)
    
        <div class="max-w-9xl mx-auto mt-8 p-8 bg-white  rounded-lg">
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
                    
                    <fieldset class="mb-8">
                        <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Professores Orientadores</legend>  
                        <div id="professores-wrapper"></div>
                        <button type="button" id="add-professor-search" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">+ Adicionar Orientador</button>
                    </fieldset>

                    <fieldset class="mb-8">
                        <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Alunos Participantes</legend>
                        
                        {{-- Aluno Logado (Proponente) --}}
                        <div class="mb-4 p-4 border rounded-md bg-gray-50">
                            <p><strong>Proponente (Aluno 1):</strong></p>
                            <p class="mt-2 text-gray-800"><strong>Nome:</strong> {{ $alunoLogado->name }}</p>
                            <p class="text-gray-800"><strong>RA:</strong> {{ $alunoLogado->ra ?? 'Não informado' }}</p>
                            <p class="text-gray-800"><strong>Curso:</strong> {{ $alunoLogado->curso->nome ?? 'Não informado' }}</p>
                        </div>
                        {{-- Busca por outros alunos --}}
                        <div id="alunos-wrapper"></div>
                        <button type="button" id="add-aluno-search" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">+ Adicionar Outro Aluno</button>
                    </fieldset>

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

                    <div id="atividades-wrapper">
                        {{-- O JavaScript irá inserir a(s) atividade(s) aqui --}}
                    </div>
                    <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade</button>

                    <label class="block mb-2 text-lg font-semibold text-blue-700">Cronograma</label>
                                <div id="cronograma-wrapper">
                                    {{-- Primeiro item do cronograma --}}
                                    <div class="border p-4 rounded-md mb-4 cronograma-item">
                                        <p><strong>Atividade do Cronograma 1</strong></p>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                            <input type="text" name="cronograma[0][atividade]" class="form-input w-full border-gray-300 rounded-md" maxlength="100" placeholder="Título da Atividade do Cronograma" value="{{ old('cronograma.0.atividade') }}" required>
                                            <select name="cronograma[0][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required>
                                                <option value="">-- Mês de Início --</option>
                                                @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $m)
                                                    <option value="{{ $m }}" {{ old('cronograma.0.mes_inicio') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                @endforeach
                                            </select>
                                            <select name="cronograma[0][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required>
                                                <option value="">-- Mês de Fim --</option>
                                                @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $m)
                                                    <option value="{{ $m }}" {{ old('cronograma.0.mes_fim') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>
                                <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade ao Cronograma</button>

                    <label class="block mb-2">7. Recursos Necessários</label>
                    <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('recursos') }}</textarea>

                    <label class="block mb-2">8. Resultados Esperados</label>
                    <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4" maxlength="1000">{{ old('resultados_esperados') }}</textarea>

                </fieldset>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">Salvar Projeto</button>
            </form>
        </div>
    @else
        <div class="max-w-7xl mx-auto mt-8 p-8 bg-white shadow-md rounded-lg">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p class="font-bold">Acesso Negado</p>
                <p>Você não tem permissão para criar novas propostas de projeto.</p>
            </div>
        </div>
    @endcan

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // =========================================================================
            // LÓGICA DE BUSCA DE ALUNOS E PROFESSORES (SEM ALTERAÇÃO)
            // =========================================================================
            const createSearchComponent = (type, pluralName, wrapperId, addButtonId) => {
                const wrapper = document.getElementById(wrapperId);
                const addButton = document.getElementById(addButtonId);
                let userCount = 0;

                const addSearchField = () => {
                    const index = userCount++;
                    const searchComponent = document.createElement('div');
                    searchComponent.classList.add('search-component', 'mb-4', 'p-4', 'border', 'rounded-md', 'relative');
                    searchComponent.dataset.index = index;

                    const placeholder = (type === 'aluno') ? 'Buscar por nome, RA ou curso...' : 'Buscar por nome ou email...';

                    searchComponent.innerHTML = `
                        <div class="flex justify-between items-center mb-2">
                            <p><strong>${type.charAt(0).toUpperCase() + type.slice(1)} ${index + 1}</strong></p>
                            ${index > 0 ? '<button type="button" class="remove-btn bg-red-600 text-white text-xs px-2 py-1 rounded">Remover</button>' : ''}
                        </div>
                        <div class="flex gap-2">
                            <input type="text" class="search-input w-full border-gray-300 rounded-md" placeholder="${placeholder}">
                        </div>
                        <ul class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10"></ul>
                        <div class="selected-user mt-2 text-sm text-gray-600"></div>
                        <input type="hidden" name="${pluralName}[]" class="user-id-input">
                    `;
                    wrapper.appendChild(searchComponent);
                };

                addButton.addEventListener('click', addSearchField);

                wrapper.addEventListener('input', async (e) => {
                    if (e.target.classList.contains('search-input')) {
                        const component = e.target.closest('.search-component');
                        const resultsList = component.querySelector('.search-results');
                        const searchTerm = e.target.value;

                        if (searchTerm.length < 3) {
                            resultsList.classList.add('hidden'); return;
                        }

                        const role = (type === 'aluno') ? 'aluno' : 'professor';
                        const response = await fetch(`{{ route('users.search') }}?search=${searchTerm}&role=${role}`);
                        const users = await response.json();

                        resultsList.innerHTML = '';
                        if (users.length > 0) {
                            users.forEach(user => {
                                const li = document.createElement('li');
                                li.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
                                li.textContent = `${user.name} (${user.ra || user.email})`;
                                li.dataset.id = user.id;
                                li.dataset.name = user.name;
                                resultsList.appendChild(li);
                            });
                            resultsList.classList.remove('hidden');
                        } else {
                            resultsList.innerHTML = '<li class="p-2 text-gray-500">Nenhum usuário encontrado.</li>';
                            resultsList.classList.remove('hidden');
                        }
                    }
                });

                wrapper.addEventListener('click', (e) => {
                    if (e.target.tagName === 'LI' && e.target.closest('.search-results')) {
                        const component = e.target.closest('.search-component');
                        const selectedDiv = component.querySelector('.selected-user');
                        const hiddenInput = component.querySelector('.user-id-input');
                        const searchInput = component.querySelector('.search-input');
                        const resultsList = component.querySelector('.search-results');
                        
                        selectedDiv.textContent = `Selecionado: ${e.target.dataset.name}`;
                        hiddenInput.value = e.target.dataset.id;
                        searchInput.value = e.target.dataset.name;
                        resultsList.classList.add('hidden');
                    }

                    if (e.target.classList.contains('remove-btn')) {
                        e.target.closest('.search-component').remove();
                        const allComponents = wrapper.querySelectorAll('.search-component');
                        allComponents.forEach((comp, i) => {
                            comp.querySelector('strong').textContent = `${type.charAt(0).toUpperCase() + type.slice(1)} ${i + 1}`;
                        });
                        userCount--;
                    }
                });

                addSearchField();
            };

            createSearchComponent('aluno', 'alunos', 'alunos-wrapper', 'add-aluno-search');
            createSearchComponent('professor', 'professores', 'professores-wrapper', 'add-professor-search');


            // =========================================================================
            // LÓGICA CORRIGIDA E ROBUSTA PARA ATIVIDADES E CRONOGRAMA
            // =========================================================================
            const setupDynamicFields = (type, wrapperId, addButtonId, templateFunction) => {
                const wrapper = document.getElementById(wrapperId);
                const addButton = document.getElementById(addButtonId);

                const reindexFields = () => {
                    const items = wrapper.children;
                    Array.from(items).forEach((item, index) => {
                        // Atualiza o título (ex: "Atividade 1", "Atividade 2")
                        item.querySelector('strong').textContent = `${type} ${index + 1}`;
                        
                        // Atualiza o 'name' de todos os inputs/textareas/selects
                        item.querySelectorAll('[name]').forEach(field => {
                            field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
                        });

                        // Mostra ou esconde o botão de remover
                        const removeBtn = item.querySelector('.remove-item-btn');
                        if (removeBtn) {
                            removeBtn.style.display = index > 0 ? 'inline-block' : 'none';
                        }
                    });
                };

                const addField = () => {
                    const index = wrapper.children.length;
                    const newField = document.createElement('div');
                    // O .firstElementChild é usado porque o template cria um container div
                    newField.innerHTML = templateFunction(index); 
                    wrapper.appendChild(newField.firstElementChild);
                    reindexFields(); // Reindexa sempre que um novo campo é adicionado
                };
                
                addButton.addEventListener('click', addField);

                wrapper.addEventListener('click', (e) => {
                    if (e.target && e.target.classList.contains('remove-item-btn')) {
                        e.target.closest('.dynamic-item').remove();
                        reindexFields(); // Reindexa sempre que um campo é removido
                    }
                });

                // ===== A CORREÇÃO PRINCIPAL ESTÁ AQUI =====
                // Garante que, se não houver nenhum campo, o primeiro seja adicionado ao carregar a página.
                if (wrapper.children.length === 0) {
                    addField();
                }
            };

            // Template para Atividades
            const atividadeTemplate = (index) => `
                <div class="mb-4 border p-3 rounded-md dynamic-item">
                    <div class="flex justify-between items-center mb-2">
                        <strong>Atividade ${index + 1}</strong>
                        <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded" style="display: ${index > 0 ? 'inline-block' : 'none'}">Remover</button>
                    </div>
                    <textarea name="atividades[${index}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required></textarea>
                    <textarea name="atividades[${index}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required></textarea>
                    <input type="number" name="atividades[${index}][carga_horaria]" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required>
                </div>`;

            // Template para Cronograma
            const cronogramaTemplate = (index) => {
                const mesesOptionsHtml = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}">${m}</option>`).join('');
                return `
                <div class="border p-4 rounded-md mb-4 dynamic-item cronograma-item">
                    <div class="flex justify-between items-center mb-2">
                        <strong>Cronograma ${index + 1}</strong>
                        <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded" style="display: ${index > 0 ? 'inline-block' : 'none'}">Remover</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                        <input type="text" name="cronograma[${index}][atividade]" class="form-input w-full border-gray-300 rounded-md" placeholder="Título da Atividade" required>
                        <select name="cronograma[${index}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Início --</option>${mesesOptionsHtml}</select>
                        <select name="cronograma[${index}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Fim --</option>${mesesOptionsHtml}</select>
                    </div>
                </div>`;
            };

            // Inicializa os campos dinâmicos
            setupDynamicFields('Atividade', 'atividades-wrapper', 'add-atividade', atividadeTemplate);
            setupDynamicFields('Cronograma', 'cronograma-wrapper', 'add-cronograma', cronogramaTemplate);


            // Validação de Data (mantida)
            document.getElementById('form-projeto').addEventListener('submit', function (e) {
                const inicio = document.getElementById('data_inicio').value;
                const fim = document.getElementById('data_fim').value;
                if (inicio && fim && new Date(inicio) > new Date(fim)) {
                    e.preventDefault();
                    alert('A data de início deve ser anterior ou igual à data de término.');
                }
            });
        });
    </script>


</x-app-layout>
