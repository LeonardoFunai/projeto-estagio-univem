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
        <div class="flex items-end justify-center space-x-6 mt-3">

    {{-- Etapas principais reduzidas --}}
    <div class="flex space-x-6 self-center">
        @foreach ([
            ['label' => 'Proposta Criada', 'classe' => 'concluida'],
            ['label' => 'Editando', 'classe' => 'atual'],
            ['label' => 'Entregue', 'classe' => 'futuro'],
        ] as $i => $etapa)
            <div class="flex flex-col items-center">
                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center 
                    @if($etapa['classe'] === 'concluida')
                        bg-green-500 text-white border-green-600 shadow
                    @elseif($etapa['classe'] === 'atual')
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
                    <h3><strong>Professor 1</strong></h3>
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
                <label class="block mb-2 text-lg font-semibold text-blue-700">6. Cronograma</label>
                <div id="cronograma-wrapper">
                    @php
                        // Garante que $projeto->cronogramas seja uma coleção (mesmo que vazia)
                        // e mapeia para o formato esperado, incluindo mes_inicio e mes_fim
                        $cronogramasDoProjeto = $projeto->cronogramas ? $projeto->cronogramas->map(function($c) {
                            return [
                                // 'id' => $c->id, // Mantenha se você usa o ID do cronograma no frontend
                                'atividade' => $c->atividade,
                                'mes_inicio' => $c->mes_inicio, // Assumindo que seu modelo Cronograma agora tem mes_inicio
                                'mes_fim' => $c->mes_fim       // Assumindo que seu modelo Cronograma agora tem mes_fim
                            ];
                        })->toArray() : [];
                        $cronogramaVelho = old('cronograma', $cronogramasDoProjeto);
                    @endphp

                    @if (!empty($cronogramaVelho))
                        @foreach ($cronogramaVelho as $index => $cronogramaItem)
                            @php
                                // Acessa os dados de forma segura, seja de old() (array) ou do modelo (objeto)
                                $atividadeValue = $cronogramaItem['atividade'] ?? '';
                                $mesInicioSelecionado = $cronogramaItem['mes_inicio'] ?? '';
                                $mesFimSelecionado = $cronogramaItem['mes_fim'] ?? '';
                            @endphp
                            <div class="border p-4 rounded-md mb-4 cronograma-item">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-semibold">Atividade do Cronograma {{ $index + 1 }}</h4>
                                    @if(!$disableAlunoFields)
                                        <button type="button" onclick="this.closest('.cronograma-item').remove(); reindexarCampos('cronograma-wrapper', 'Atividade do Cronograma', 'cronograma');" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remover</button>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                    <input type="text" name="cronograma[{{ $index }}][atividade]" value="{{ $atividadeValue }}"
                                        class="form-input w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'bg-gray-100 opacity-70 cursor-not-allowed' : '' }}"
                                        {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="100" required placeholder="Título da Atividade">

                                    <select name="cronograma[{{ $index }}][mes_inicio]"
                                            class="form-select w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'bg-gray-100 opacity-70 cursor-not-allowed' : '' }}"
                                            {{ $disableAlunoFields ? 'disabled' : '' }} required>
                                        <option value="">-- Mês de Início --</option>
                                        @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $m)
                                            <option value="{{ $m }}" {{ $mesInicioSelecionado === $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>

                                    <select name="cronograma[{{ $index }}][mes_fim]"
                                            class="form-select w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                                            {{ $disableAlunoFields ? 'disabled' : '' }} required>
                                        <option value="">-- Mês de Fim --</option>
                                        @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $m)
                                            <option value="{{ $m }}" {{ $mesFimSelecionado === $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- Opcional: Mostrar o primeiro item vazio se não houver dados 'old' nem do projeto --}}
                        {{-- Se você quiser que o primeiro item sempre apareça, mesmo na edição de um projeto sem cronogramas,
                            você pode descomentar e adaptar o bloco abaixo, similar ao formulário de criação.
                            Caso contrário, o wrapper começará vazio e o usuário usará o botão "+ Adicionar".
                        <div class="border p-4 rounded-md mb-4 cronograma-item">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold">Atividade do Cronograma 1</h4>
                                Nenhum botão de remover para o primeiro item se ele for estático
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <input type="text" name="cronograma[0][atividade]" class="form-input w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'bg-gray-100 opacity-70 cursor-not-allowed' : '' }}" {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="100" required placeholder="Título da Atividade">
                                <select name="cronograma[0][mes_inicio]" class="form-select w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'bg-gray-100 opacity-70 cursor-not-allowed' : '' }}" {{ $disableAlunoFields ? 'disabled' : '' }} required>
                                    <option value="">-- Mês de Início --</option>
                                    @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <select name="cronograma[0][mes_fim]" class="form-select w-full border-gray-300 rounded-md {{ $disableAlunoFields ? 'bg-gray-100 opacity-70 cursor-not-allowed' : '' }}" {{ $disableAlunoFields ? 'disabled' : '' }} required>
                                    <option value="">-- Mês de Fim --</option>
                                    @foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        --}}
                    @endif
                </div>

                @if(!$disableAlunoFields)
                    <button type="button" id="add-cronograma" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                        + Adicionar Atividade ao Cronograma
                    </button>
                @endif

                <!-- Campo: Recursos Necessários -->
                <label class="block mb-2">7. Recursos Necessários</label>
                <textarea name="recursos"
                    class="w-full border-gray-300 rounded-md mb-4 {{ $disableAlunoFields ? 'opacity-50' : '' }}"
                    {{ $disableAlunoFields ? 'readonly disabled' : '' }} maxlength="1000">{{ old('recursos', $projeto->recursos) }}</textarea>

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
        // Contadores para garantir índices únicos ao adicionar dinamicamente ANTES da reindexação
        // A contagem real para exibição (Aluno 1, Aluno 2) e para os índices do array após remoção
        // será feita pela função reindexarCampos baseada nos elementos existentes.
        let initialProfessorCount = {{ count(old('professores', $projeto->professores->toArray())) }};
        let initialAlunoCount = {{ count(old('alunos', $projeto->alunos->toArray())) }};
        let initialAtividadeCount = {{ count(old('atividades', $projeto->atividades->toArray())) }};
        let initialCronogramaCount = {{ count(old('cronograma', $projeto->cronogramas->toArray())) }};

        // Opções de professores (do seu script original, mas mais robusto se o select não existir inicialmente)
        const selectProfessoresEl = document.querySelector('select[name^="professores["][name$="[id]"]');
        const professorOptions = selectProfessoresEl ? `
            <option value="">-- Selecione um professor --</option>
            ${Array.from(selectProfessoresEl.options)
                .slice(1) // Pula a primeira opção "-- Selecione um professor --" do original
                .map(option => `<option value="${option.value}">${option.text}</option>`)
                .join('')}
        ` : '<option value="">Professores não carregados</option>';

        // Lista de todos os meses para os selects do cronograma
        const todosOsMeses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        const mesesOptionsHtml = todosOsMeses.map(m => `<option value="${m}">${m}</option>`).join('');

        function reindexarCampos(wrapperId, prefixoH4, nameBase) {
            const items = document.querySelectorAll(`#${wrapperId} > .mb-4, #${wrapperId} > .cronograma-item`); // Suporta ambas as classes
            items.forEach((div, i) => {
                const h4 = div.querySelector('h4');
                if (h4 && prefixoH4) {
                    h4.textContent = `${prefixoH4} ${i + 1}`;
                } else if (h4 && wrapperId === 'cronograma-wrapper' && !prefixoH4) {
                    h4.textContent = `Atividade do Cronograma ${i + 1}`;
                }

                const inputsEselects = div.querySelectorAll('input[name], select[name], textarea[name]');
                inputsEselects.forEach(field => {
                    const nameAttr = field.getAttribute('name');
                    const matches = nameAttr.match(/\[\d+\]\[(\w+)]$/); // Captura a chave final
                    if (matches && matches[1]) {
                        field.setAttribute('name', `${nameBase}[${i}][${matches[1]}]`);
                    }
                });
            });
        }

        document.getElementById('add-professor')?.addEventListener('click', () => {
            const wrapper = document.getElementById('professores-wrapper');
            const currentItemCount = wrapper.querySelectorAll('.mb-4').length; // Conta os itens existentes
            if (currentItemCount < 9) { // Limite de exemplo
                const div = document.createElement('div');
                div.classList.add('mb-4', 'border', 'p-3', 'rounded-md'); // Adicionando classes para consistência
                // Usamos currentItemCount para o próximo índice, pois a reindexação ajustará
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold">Professor ${currentItemCount + 1}</h4>
                        <button type="button" onclick="this.closest('.mb-4').remove(); reindexarCampos('professores-wrapper', 'Professor', 'professores');" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remover</button>
                    </div>
                    <select name="professores[${currentItemCount}][id]" class="w-full border-gray-300 rounded-md mb-2" required>
                        ${professorOptions}
                    </select>
                    <input type="text" name="professores[${currentItemCount}][area]" class="w-full border-gray-300 rounded-md" maxlength="100" placeholder="Área (opcional)">
                `;
                wrapper.appendChild(div);
                reindexarCampos('professores-wrapper', 'Professor', 'professores');
            }
        });

        document.getElementById('add-aluno')?.addEventListener('click', () => {
            const wrapper = document.getElementById('alunos-wrapper');
            const currentItemCount = wrapper.querySelectorAll('.mb-4').length;
            if (currentItemCount < 9) { // Limite de exemplo
                const div = document.createElement('div');
                div.classList.add('mb-4', 'border', 'p-3', 'rounded-md');
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold">Aluno ${currentItemCount + 1}</h4>
                        <button type="button" onclick="this.closest('.mb-4').remove(); reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remover</button>
                    </div>
                    <input type="text" name="alunos[${currentItemCount}][nome]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Nome do aluno" maxlength="100" required>
                    <input type="text" name="alunos[${currentItemCount}][ra]" class="w-full border-gray-300 rounded-md mb-2" placeholder="RA" maxlength="50" required>
                    <input type="text" name="alunos[${currentItemCount}][curso]" class="w-full border-gray-300 rounded-md" placeholder="Curso" maxlength="100" required>
                `;
                wrapper.appendChild(div);
                reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');
            }
        });

        document.getElementById('add-atividade')?.addEventListener('click', () => {
            const wrapper = document.getElementById('atividades-wrapper');
            const currentItemCount = wrapper.querySelectorAll('.mb-4').length;
            const div = document.createElement('div');
            div.classList.add('mb-4', 'border', 'p-3', 'rounded-md');
            div.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold">Atividade ${currentItemCount + 1}</h4>
                    <button type="button" onclick="this.closest('.mb-4').remove(); reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <label class="block mb-1 text-sm font-medium text-gray-700">O que fazer?</label>
                <textarea name="atividades[${currentItemCount}][o_que_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="O que fazer?" maxlength="1000" required></textarea>
                <label class="block mb-1 text-sm font-medium text-gray-700">Como fazer?</label>
                <textarea name="atividades[${currentItemCount}][como_fazer]" class="w-full border-gray-300 rounded-md mb-2" placeholder="Como fazer?" maxlength="1000" required></textarea>
                <label class="block mb-1 text-sm font-medium text-gray-700">Carga horária (horas):</label>
                <input type="number" name="atividades[${currentItemCount}][carga_horaria]" class="w-full border-gray-300 rounded-md" min="1" max="99999" placeholder="Carga horária" required>
            `;
            wrapper.appendChild(div);
            reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');
        });

        // MODIFICADO: Adicionar Cronograma com Mês de Início e Mês de Fim
        document.getElementById('add-cronograma')?.addEventListener('click', () => {
            const wrapper = document.getElementById('cronograma-wrapper');
            const currentItemCount = wrapper.querySelectorAll('.cronograma-item').length;
            const divWrapper = document.createElement('div');
            divWrapper.classList.add('border', 'p-4', 'rounded-md', 'mb-4', 'cronograma-item');

            divWrapper.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold">Atividade do Cronograma ${currentItemCount + 1}</h4>
                    <button type="button" onclick="this.closest('.cronograma-item').remove(); reindexarCampos('cronograma-wrapper', 'Atividade do Cronograma', 'cronograma');" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Remover</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <input type="text" name="cronograma[${currentItemCount}][atividade]" class="form-input w-full border-gray-300 rounded-md" placeholder="Título da Atividade do Cronograma" maxlength="100" required>
                    <select name="cronograma[${currentItemCount}][mes_inicio]" class="form-select w-full border-gray-300 rounded-md" required>
                        <option value="">-- Mês de Início --</option>
                        ${mesesOptionsHtml}
                    </select>
                    <select name="cronograma[${currentItemCount}][mes_fim]" class="form-select w-full border-gray-300 rounded-md" required>
                        <option value="">-- Mês de Fim --</option>
                        ${mesesOptionsHtml}
                    </select>
                </div>
            `;
            wrapper.appendChild(divWrapper);
            reindexarCampos('cronograma-wrapper', 'Atividade do Cronograma', 'cronograma');
        });

        // Validação de Data (do seu script original)
        document.getElementById('form-projeto')?.addEventListener('submit', function (e) {
            const inicio = document.getElementById('data_inicio').value;
            const fim = document.getElementById('data_fim').value;
            if (inicio && fim && new Date(inicio) > new Date(fim)) {
                e.preventDefault();
                alert('A data de início deve ser anterior ou igual à data de término.');
            }
        });

        // Chama reindexar para todos os campos no carregamento inicial para garantir que os H4s
        // e os nomes dos campos dos itens carregados pelo PHP/Blade estejam corretos.
        // É importante que o HTML gerado pelo Blade para os itens existentes também
        // tenha a classe 'cronograma-item' (para cronogramas) ou 'mb-4' (para os outros) no div principal do item.
        document.addEventListener('DOMContentLoaded', function() {
            reindexarCampos('professores-wrapper', 'Professor', 'professores');
            reindexarCampos('alunos-wrapper', 'Aluno', 'alunos');
            reindexarCampos('atividades-wrapper', 'Atividade', 'atividades');
            reindexarCampos('cronograma-wrapper', 'Atividade do Cronograma', 'cronograma');
        });

    </script>


</x-app-layout>
