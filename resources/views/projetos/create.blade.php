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
                                    {{-- O JavaScript irá inserir o(s) cronograma(s) aqui --}}
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

        /**
         * Função genérica para criar um componente de busca e convite.
         * @param {string} role - 'aluno' ou 'professor'.
         * @param {string} searchInputId - ID do campo de input da busca.
         * @param {string} resultsListId - ID da lista <ul> para os resultados.
         * @param {string} invitationsListId - ID do div para a lista visual de convites.
         * @param {string} hiddenContainerId - ID do div que guardará os inputs hidden.
         */
        const setupInvitationSearch = (role, searchInputId, resultsListId, invitationsListId, hiddenContainerId) => {
            const searchInput = document.getElementById(searchInputId);
            const resultsList = document.getElementById(resultsListId);
            const invitationsList = document.getElementById(invitationsListId);
            const hiddenContainer = document.getElementById(hiddenContainerId);
            const selectedUserIds = new Set();

            // Adiciona o ID do usuário logado para não aparecer na busca de alunos
            if (role === 'aluno') {
                const loggedInUserId = "{{ auth()->id() }}";
                selectedUserIds.add(parseInt(loggedInUserId));
            }

            // Evento de digitação no campo de busca
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
                            if (selectedUserIds.has(user.id)) return; // Não mostra usuários já selecionados

                            const li = document.createElement('li');
                            li.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
                            
                            const courseName = user.curso ? user.curso.nome : 'N/A';
                            li.textContent = (role === 'aluno') 
                                ? `${user.name} (RA: ${user.ra || 'N/A'})`
                                : `${user.name} (${user.email})`;
                            
                            li.dataset.id = user.id;
                            li.dataset.name = user.name;
                            li.dataset.email = user.email;
                            
                            resultsList.appendChild(li);
                        });
                    } else {
                        resultsList.innerHTML = '<li class="p-2 text-gray-500">Nenhum usuário encontrado.</li>';
                    }
                    resultsList.classList.remove('hidden');
                } catch (error) {
                    console.error('Erro na busca:', error);
                    resultsList.innerHTML = '<li class="p-2 text-red-500">Erro ao buscar.</li>';
                }
            });

            // Evento de clique em um resultado da busca
            resultsList.addEventListener('click', (e) => {
                if (e.target.tagName !== 'LI' || !e.target.dataset.id) return;

                const user = e.target.dataset;
                const index = `user_${user.id}`;

                // 1. Adiciona o ID ao set de selecionados
                selectedUserIds.add(parseInt(user.id));

                // 2. Cria a representação visual na lista de convites
                const listItem = document.createElement('div');
                listItem.id = `item_${index}`;
                listItem.className = 'dynamic-item flex items-center justify-between p-2 border rounded-md bg-gray-50';
                listItem.innerHTML = `
                    <span>${user.name}</span>
                    <button type="button" class="remove-invitation-btn text-red-500 hover:text-red-700 font-bold" data-index="${index}" data-id="${user.id}">&times;</button>
                `;
                invitationsList.appendChild(listItem);

                // 3. Cria os inputs hidden que serão enviados com o formulário
                const hiddenInputs = document.createElement('div');
                hiddenInputs.id = `hidden_${index}`;
                hiddenInputs.innerHTML = `
                    <input type="hidden" name="invitations[${index}][email]" value="${user.email}">
                    <input type="hidden" name="invitations[${index}][role]" value="${role}">
                `;
                hiddenContainer.appendChild(hiddenInputs);

                // 4. Limpa e esconde a busca
                searchInput.value = '';
                resultsList.classList.add('hidden');
            });

            // Evento para o botão de remover
            document.body.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-invitation-btn')) {
                    const index = e.target.dataset.index;
                    const userId = e.target.dataset.id;

                    document.getElementById(`item_${index}`)?.remove();
                    document.getElementById(`hidden_${index}`)?.remove();
                    selectedUserIds.delete(parseInt(userId));
                }
            });
        };

        // Inicializa os dois componentes de busca e convite
        setupInvitationSearch('professor', 'orientador-search-input', 'orientador-search-results', 'orientadores-invitations-list', 'invitations-hidden-inputs');
        setupInvitationSearch('aluno', 'aluno-search-input', 'aluno-search-results', 'alunos-invitations-list', 'invitations-hidden-inputs');

        // --- MANTER O RESTANTE DO SEU SCRIPT (ATIVIDADES, CRONOGRAMA, DATAS) ---
        // ... cole o resto do seu script para atividades, etc., aqui ...
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
                    if (removeBtn) {
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
            }
        };

        const atividadeTemplate = (index) => `
            <div class="mb-4 border p-3 rounded-md dynamic-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded" style="display: ${index > 0 ? 'inline-block' : 'none'}">Remover</button>
                </div>
                <textarea name="atividades[${index}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" required maxlength="1000"></textarea>
                <textarea name="atividades[${index}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" required maxlength="1000"></textarea>
                <input type="number" name="atividades[${index}][carga_horaria]" class="w-full border-gray-300 rounded-md" placeholder="Carga horária" required min="1" max="99999">
            </div>`;
        
        const cronogramaTemplate = (index) => {
            const mesesOptionsHtml = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'].map(m => `<option value="${m}">${m}</option>`).join('');
            return `
            <div class="border p-4 rounded-md mb-4 dynamic-item cronograma-item">
                <div class="flex justify-between items-center mb-2">
                    <strong>Atividade do Cronograma ${index + 1}</strong>
                    <button type="button" class="remove-item-btn bg-red-600 text-white text-xs py-1 px-2 rounded" style="display: ${index > 0 ? 'inline-block' : 'none'}">Remover</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <input type="text" name="cronograma[${index}][atividade]" class="form-input w-full border-gray-300 rounded-md" placeholder="Título da Atividade" required maxlength="100">
                    <select name="cronograma[${index}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Início --</option>${mesesOptionsHtml}</select>
                    <select name="cronograma[${index}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required><option value="">-- Mês de Fim --</option>${mesesOptionsHtml}</select>
                </div>
            </div>`;
        };

        setupDynamicFields('Atividade', 'atividades-wrapper', 'add-atividade', atividadeTemplate);
        setupDynamicFields('Atividade do Cronograma', 'cronograma-wrapper', 'add-cronograma', cronogramaTemplate);

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