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
                    
                    {{-- NOVO: Seção para Convidar Orientadores com Busca --}}
                    <fieldset class="mb-8">
                        <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Orientadores</legend>
                        <div id="orientador-search-component">
                            <div class="search-container mb-2 relative">
                                <input type="text" id="orientador-search-input" class="w-full border-gray-300 rounded-md" placeholder="Buscar por nome ou email do orientador...">
                                <ul id="orientador-search-results" class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10"></ul>
                            </div>
                            <div id="orientadores-invitations-list" class="space-y-2">
                                {{-- Lista de orientadores selecionados para convite --}}
                            </div>
                        </div>
                    </fieldset>

                    {{-- NOVO: Seção para Convidar Alunos com Busca --}}
                    <fieldset class="mb-8">
                        <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Outros Alunos Participantes</legend>

                        {{-- Exibe o aluno que está criando o projeto --}}
                        <div class="mb-4 p-4 border rounded-md bg-gray-50">
                            <p><strong>Proponente (Aluno 1):</strong> {{ $alunoLogado->name }} ({{ $alunoLogado->ra }})</p>
                        </div>

                        <div id="aluno-search-component">
                            <div class="search-container mb-2 relative">
                                <input type="text" id="aluno-search-input" class="w-full border-gray-300 rounded-md" placeholder="Buscar por nome ou R.A. do aluno...">
                                <ul id="aluno-search-results" class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10"></ul>
                            </div>
                            <div id="alunos-invitations-list" class="space-y-2">
                                {{-- Lista de alunos selecionados para convite --}}
                            </div>
                        </div>
                    </fieldset>

                    {{-- Container para os inputs hidden dos convites --}}
                    <div id="invitations-hidden-inputs"></div>

                    <label class="block mb-2">Público Alvo:</label>
                    <textarea name="publico_alvo" class="w-full border-gray-300 rounded-md mb-1" placeholder="População em Geral" maxlength="1000">{{ old('publico_alvo') }} </textarea>

                    <label class="block mb-2">Data de Início:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_inicio') }}" required>

                    <label class="block mb-2">Data de Término:</label>
                    <input type="date" name="data_fim" id="data_fim" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('data_fim') }}" required>
                </fieldset>

                <fieldset class="mb-8">
                    <legend class="text-lg font-semibold text-blue-700 mb-4">Detalhes do Projeto</legend>

                    <label class="block mb-2">1. Introdução</label>
                    <textarea name="introducao" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('introducao') }}</textarea>

                    <label class="block mb-2">2. Objetivos do Projeto</label>
                    <textarea name="objetivo_geral" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('objetivo_geral') }}</textarea>


                    <label class="block mb-2">3. Justificativa</label>
                    <textarea name="justificativa" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('justificativa') }}</textarea>


                    <label class="block mb-2">4. Metodologia</label>
                    <textarea name="metodologia" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('metodologia') }}</textarea>

                    <label class="block mb-2">5. Atividades a serem desenvolvidas</label>
                    <small class="block mb-2 text-gray-600">(O que fazer, como fazer e carga horária)</small>

                    <div id="atividades-wrapper">
                        {{-- O JavaScript irá inserir a(s) atividade(s) aqui --}}
                    </div>
                    <button type="button" id="add-atividade" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade</button>

                    <label class="block mb-2 text-lg font-semibold text-blue-700">Cronograma</label>
                                <div id="cronograma-wrapper">
                                    {{-- O JavaScript irá inserir o(s) cronograma(s) aqui --}}
                                </div>
                                <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">+ Adicionar Atividade ao Cronograma</button>

                    <label class="block mb-2">7. Recursos Necessários</label>
                    <textarea name="recursos" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('recursos') }}</textarea>

                    <label class="block mb-2">8. Resultados Esperados</label>
                    <textarea name="resultados_esperados" class="w-full border-gray-300 rounded-md mb-4" maxlength="15000">{{ old('resultados_esperados') }}</textarea>

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

        // -------------------------------------------------------------------
        // PARTE 1: MANIPULAÇÃO DE CAMPOS DINÂMICOS (ATIVIDADES E CRONOGRAMA)
        // -------------------------------------------------------------------

        const setupDynamicFields = (type, wrapperId, addButtonId, templateFunction) => {
            const wrapper = document.getElementById(wrapperId);
            const addButton = document.getElementById(addButtonId);

            const reindexFields = () => {
                const items = wrapper.children;
                Array.from(items).forEach((item, index) => {
                    item.querySelector('strong').textContent = `${type} ${index + 1}`;
                    item.querySelectorAll('[name]').forEach(field => {
                        field.name = field.name.replace(/\[\d+\]/g, `[${index}]`);
                    });
                    const removeBtn = item.querySelector('.remove-item-btn');
                    if (removeBtn) {
                        removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                    }
                });
            };

            const addField = (data = {}) => {
                const index = wrapper.children.length;
                const newField = document.createElement('div');
                // A função de template agora recebe os dados para preenchimento
                newField.innerHTML = templateFunction(index, data);
                wrapper.appendChild(newField.firstElementChild);
                reindexFields();
            };

            addButton.addEventListener('click', () => addField());

            wrapper.addEventListener('click', (e) => {
                if (e.target && e.target.classList.contains('remove-item-btn')) {
                    e.target.closest('.dynamic-item').remove();
                    reindexFields();
                }
            });

            // Retorna a função addField para ser usada pela lógica de repopulação
            return { addField };
        };

        // TEMPLATES com lógica para preencher dados
        const atividadeTemplate = (index, data = {}) => `
            <div class="mb-4 border p-3 rounded-md dynamic-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <textarea name="atividades[${index}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required maxlength="15000">${data.o_que_fazer || ''}</textarea>
                <textarea name="atividades[${index}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required maxlength="15000">${data.como_fazer || ''}</textarea>
                <input type="number" name="atividades[${index}][carga_horaria]" value="${data.carga_horaria || ''}" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required min="1" max="99999">
            </div>`;

        const cronogramaTemplate = (index, data = {}) => {
            const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
            const mesesInicioHtml = meses.map(m => `<option value="${m}" ${data.mes_inicio === m ? 'selected' : ''}>${m}</option>`).join('');
            const mesesFimHtml = meses.map(m => `<option value="${m}" ${data.mes_fim === m ? 'selected' : ''}>${m}</option>`).join('');
            return `
            <div class="border p-4 rounded-md mb-4 dynamic-item cronograma-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade do Cronograma ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <input type="text" name="cronograma[${index}][atividade]" value="${data.atividade || ''}" class="form-input w-full border-gray-300 rounded-md" placeholder="Título da Atividade" required maxlength="100">
                    <select name="cronograma[${index}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Início --</option>${mesesInicioHtml}</select>
                    <select name="cronograma[${index}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Fim --</option>${mesesFimHtml}</select>
                </div>
            </div>`;
        };

        // Inicializa os gerenciadores
        const atividadesManager = setupDynamicFields('Atividade', 'atividades-wrapper', 'add-atividade', atividadeTemplate);
        const cronogramaManager = setupDynamicFields('Atividade do Cronograma', 'cronograma-wrapper', 'add-cronograma', cronogramaTemplate);


        // -------------------------------------------------------------------
        // PARTE 2: BUSCA E ADIÇÃO DE PARTICIPANTES
        // -------------------------------------------------------------------

        const selectedUsers = {
            professor: new Set(),
            aluno: new Set()
        };

        const addUserToView = (user, role) => {
            const invitationsList = document.getElementById(role === 'professor' ? 'orientadores-invitations-list' : 'alunos-invitations-list');
            const hiddenContainer = document.getElementById('invitations-hidden-inputs');
            const index = `user_${user.id}`;

            if (document.getElementById(`item_${index}`)) return;

            selectedUsers[role].add(parseInt(user.id));

            const listItem = document.createElement('div');
            listItem.id = `item_${index}`;
            listItem.className = 'dynamic-item flex items-center justify-between p-2 border rounded-md bg-gray-50';
            listItem.innerHTML = `<span>${user.name}</span><button type="button" class="remove-invitation-btn text-red-500 hover:text-red-700 font-bold" data-role="${role}" data-index="${index}" data-id="${user.id}">&times;</button>`;
            invitationsList.appendChild(listItem);

            const hiddenInputs = document.createElement('div');
            hiddenInputs.id = `hidden_${index}`;
            hiddenInputs.innerHTML = `<input type="hidden" name="invitations[${index}][email]" value="${user.email}"><input type="hidden" name="invitations[${index}][role]" value="${role}">`;
            hiddenContainer.appendChild(hiddenInputs);
        };

        const setupInvitationSearch = (role, searchInputId, resultsListId, invitationsListId) => {
            const searchInput = document.getElementById(searchInputId);
            const resultsList = document.getElementById(resultsListId);
            
            if (role === 'aluno') {
                selectedUsers.aluno.add({{ auth()->id() }});
            }

            searchInput.addEventListener('input', async () => {
                const searchTerm = searchInput.value;
                if (searchTerm.length < 3) {
                    resultsList.classList.add('hidden');
                    return;
                }
                try {
                    const response = await fetch(`{{ route('users.search') }}?search=${searchTerm}&role=${role}`);
                    const users = await response.json();
                    resultsList.innerHTML = '';
                    if (users.length > 0) {
                        users.forEach(user => {
                            if (selectedUsers[role].has(user.id)) return;
                            const li = document.createElement('li');
                            li.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
                            li.textContent = role === 'aluno' ? `${user.name} (RA: ${user.ra || 'N/A'})` : `${user.name} (${user.email})`;
                            li.dataset.user = JSON.stringify(user);
                            resultsList.appendChild(li);
                        });
                    } else {
                        resultsList.innerHTML = '<li class="p-2 text-gray-500">Nenhum usuário encontrado.</li>';
                    }
                    resultsList.classList.remove('hidden');
                } catch (error) {
                    console.error('Erro na busca:', error);
                }
            });

            resultsList.addEventListener('click', (e) => {
                if (e.target.tagName !== 'LI' || !e.target.dataset.user) return;
                const user = JSON.parse(e.target.dataset.user);
                addUserToView(user, role);
                searchInput.value = '';
                resultsList.classList.add('hidden');
            });
        };

        document.body.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-invitation-btn')) {
                const { role, index, id } = e.target.dataset;
                document.getElementById(`item_${index}`)?.remove();
                document.getElementById(`hidden_${index}`)?.remove();
                selectedUsers[role].delete(parseInt(id));
            }
        });

        setupInvitationSearch('professor', 'orientador-search-input', 'orientador-search-results', 'orientadores-invitations-list');
        setupInvitationSearch('aluno', 'aluno-search-input', 'aluno-search-results', 'alunos-invitations-list');


        // -------------------------------------------------------------------
        // PARTE 3: REPOPULAÇÃO DO FORMULÁRIO AO CARREGAR A PÁGINA
        // -------------------------------------------------------------------

        const repopulateForm = () => {
            // Repopula Atividades
            const atividadesAntigas = @json(old('atividades'));
            if (Array.isArray(atividadesAntigas) && atividadesAntigas.length > 0) {
                atividadesAntigas.forEach(data => atividadesManager.addField(data));
            } else {
                atividadesManager.addField(); // Adiciona um campo inicial se não houver erro
            }

            // Repopula Cronograma
            const cronogramasAntigos = @json(old('cronograma'));
            if (Array.isArray(cronogramasAntigos) && cronogramasAntigos.length > 0) {
                cronogramasAntigos.forEach(data => cronogramaManager.addField(data));
            } else {
                cronogramaManager.addField(); // Adiciona um campo inicial se não houver erro
            }

            // Repopula Convites
            const orientadoresAntigos = @json($orientadoresSelecionados ?? []);
            const alunosAntigos = @json($alunosSelecionados ?? []);

            orientadoresAntigos.forEach(user => addUserToView(user, 'professor'));
            alunosAntigos.forEach(user => addUserToView(user, 'aluno'));
        };

        repopulateForm(); // Executa a repopulação


        // -------------------------------------------------------------------
        // PARTE 4: VALIDAÇÃO FINAL
        // -------------------------------------------------------------------
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