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
            <legend class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Gerenciar Participantes</legend>

            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-2">Orientadores</h3>
                <div class="space-y-2 mb-4">
                    @foreach ($orientadores as $orientador)
                        <div class="flex items-center justify-between p-2 border rounded-md bg-green-50 text-green-800">
                            <span>{{ $orientador->name }} ({{ $orientador->email }})</span>
                            <span class="text-xs font-bold">CONFIRMADO</span>
                        </div>
                    @endforeach
                </div>
                <div id="orientador-search-component">
                    <div class="search-container mb-2 relative">
                        <input type="text" id="orientador-search-input" class="w-full border-gray-300 rounded-md" placeholder="Buscar e convidar novo orientador...">
                        <ul id="orientador-search-results" class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10"></ul>
                    </div>
                    <div id="orientadores-invitations-list" class="space-y-2">
                        {{-- Lista de novos orientadores selecionados para convite --}}
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-2">Alunos</h3>
                <div class="space-y-2 mb-4">
                    @foreach ($alunos as $aluno)
                        <div class="flex items-center justify-between p-2 border rounded-md bg-green-50 text-green-800">
                            <span>{{ $aluno->name }} ({{ $aluno->ra ?? $aluno->email }})</span>
                            @if($aluno->id === $projeto->user_id) 
                                <span class="text-xs font-bold text-blue-600">PROPONENTE</span> 
                            @else
                                <span class="text-xs font-bold">CONFIRMADO</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div id="aluno-search-component">
                    <div class="search-container mb-2 relative">
                        <input type="text" id="aluno-search-input" class="w-full border-gray-300 rounded-md" placeholder="Buscar e convidar novo aluno...">
                        <ul id="aluno-search-results" class="search-results mt-1 border rounded max-h-48 overflow-y-auto hidden absolute bg-white w-full z-10"></ul>
                    </div>
                    <div id="alunos-invitations-list" class="space-y-2">
                        {{-- Lista de novos alunos selecionados para convite --}}
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-md font-semibold text-gray-800 mb-2">Convites Pendentes</h3>
                <div class="space-y-2">
                    @forelse ($convitesPendentes as $convite)
                        <div class="flex items-center justify-between p-2 border rounded-md bg-yellow-50 text-yellow-800">
                            <span>{{ $convite->email }} (Convidado como {{ $convite->role }})</span>
                            <span class="text-xs font-bold">PENDENTE</span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Nenhum convite pendente.</p>
                    @endforelse
                </div>
            </div>
        </fieldset>

        {{-- Container para os inputs hidden dos NOVOS convites --}}
        <div id="invitations-hidden-inputs"></div>

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

            /**
             * Função para criar o componente de busca e convite na tela de EDIÇÃO.
             * Ela já exclui participantes existentes e convites pendentes da busca.
             */
            const setupInvitationSearch = (role, searchInputId, resultsListId, invitationsListId, hiddenContainerId) => {
                const searchInput = document.getElementById(searchInputId);
                const resultsList = document.getElementById(resultsListId);
                const invitationsList = document.getElementById(invitationsListId);
                const hiddenContainer = document.getElementById(hiddenContainerId);
                
                // Pré-popula com emails de usuários já no projeto ou com convites pendentes para não serem buscados novamente.
                const existingUserEmails = @json($projeto->users->pluck('email')->merge($convitesPendentes->pluck('email')));
                const selectedUserEmails = new Set(existingUserEmails);

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
                        const foundUsers = users.filter(user => !selectedUserEmails.has(user.email));

                        if (foundUsers.length > 0) {
                            foundUsers.forEach(user => {
                                const li = document.createElement('li');
                                li.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
                                li.textContent = (role === 'aluno') ? `${user.name} (RA: ${user.ra || 'N/A'})` : `${user.name} (${user.email})`;
                                li.dataset.email = user.email;
                                li.dataset.name = user.name;
                                resultsList.appendChild(li);
                            });
                        } else {
                            resultsList.innerHTML = '<li class="p-2 text-gray-500">Nenhum usuário novo encontrado.</li>';
                        }
                        resultsList.classList.remove('hidden');
                    } catch (error) {
                        console.error('Erro na busca:', error);
                    }
                });

                // Evento de clique em um resultado da busca
                resultsList.addEventListener('click', (e) => {
                    if (e.target.tagName !== 'LI' || !e.target.dataset.email) return;

                    const user = e.target.dataset;
                    const index = `new_${user.email.replace(/[^a-zA-Z0-9]/g, '')}`;

                    selectedUserEmails.add(user.email);

                    // Cria o item visual na lista de "a convidar"
                    const listItem = document.createElement('div');
                    listItem.id = `item_${index}`;
                    listItem.className = 'dynamic-item flex items-center justify-between p-2 border rounded-md bg-blue-50 text-blue-800';
                    listItem.innerHTML = `
                        <span>${user.name}</span>
                        <button type="button" class="remove-invitation-btn text-red-500 hover:text-red-700 font-bold" data-index="${index}" data-email="${user.email}">&times;</button>
                    `;
                    invitationsList.appendChild(listItem);

                    // Cria os inputs hidden para enviar com o formulário de update
                    const hiddenInputs = document.createElement('div');
                    hiddenInputs.id = `hidden_${index}`;
                    hiddenInputs.innerHTML = `
                        <input type="hidden" name="invitations[${index}][email]" value="${user.email}">
                        <input type="hidden" name="invitations[${index}][role]" value="${role}">
                    `;
                    hiddenContainer.appendChild(hiddenInputs);

                    searchInput.value = '';
                    resultsList.classList.add('hidden');
                });

                // Evento para o botão de remover um convite da lista
                document.body.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-invitation-btn')) {
                        const index = e.target.dataset.index;
                        const email = e.target.dataset.email;
                        document.getElementById(`item_${index}`)?.remove();
                        document.getElementById(`hidden_${index}`)?.remove();
                        selectedUserEmails.delete(email);
                    }
                });
            };

            // Inicializa os componentes de busca para orientadores e alunos
            setupInvitationSearch('professor', 'orientador-search-input', 'orientador-search-results', 'orientadores-invitations-list', 'invitations-hidden-inputs');
            setupInvitationSearch('aluno', 'aluno-search-input', 'aluno-search-results', 'alunos-invitations-list', 'invitations-hidden-inputs');

            // --- LÓGICA PARA ATIVIDADES E CRONOGRAMA (MANTIDA) ---
            const setupDynamicFields = (type, wrapperId, addButtonId, templateFunction) => {
                const wrapper = document.getElementById(wrapperId);
                const addButton = document.getElementById(addButtonId);

                const reindexFields = () => {
                    const items = wrapper.children;
                    Array.from(items).forEach((item, index) => {
                        const strongTag = item.querySelector('strong');
                        if (strongTag) {
                            strongTag.textContent = `${type} ${index + 1}`;
                        }
                        
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
                
                if (addButton) {
                    addButton.addEventListener('click', addField);
                }

                wrapper.addEventListener('click', (e) => {
                    if (e.target && e.target.classList.contains('remove-item-btn')) {
                        e.target.closest('.dynamic-item').remove();
                        reindexFields();
                    }
                });
                
                if (wrapper.children.length === 0) {
                    if (addButton) {
                        addField();
                    }
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