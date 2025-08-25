@if(in_array(auth()->user()->role, ['aluno', 'professor']))
    <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do NAPEx</h2>
    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-6">
        <tbody>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Número do Projeto</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                </td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
        <tbody>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                </td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Aprovação</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                </td>
            </tr>
        </tbody>
    </table>
@endif


@if(auth()->user()->role === 'napex')
    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do Coordenador</h2>
    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
        <tbody>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Aprovação</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
    @if(request('editar') === 'napex' || (is_null($projeto->aprovado_napex) && auth()->user()->can('approveByNapex', $projeto)))
        <form id="form-parecer-napex" method="POST" action="{{ route('projetos.avaliar.napex', $projeto->id) }}" class="mb-10">
            @csrf
            <label>Número do Projeto</label>
            <input type="text" name="numero_projeto" class="w-full border-gray-300 rounded-md mb-4" value="{{ old('numero_projeto', $projeto->numero_projeto) }}">
            <label>Aprovação</label>
            <select name="aprovado_napex" class="w-full border-gray-300 rounded-md mb-4">
                <option value="">Selecione</option>
                <option value="sim" {{ $projeto->aprovado_napex == 'sim' ? 'selected' : '' }}>Sim</option>
                <option value="nao" {{ $projeto->aprovado_napex == 'nao' ? 'selected' : '' }}>Não</option>
            </select>
            <label>Motivo</label>
            <textarea name="motivo_napex" class="w-full border-gray-300 rounded-md mb-4">{{ old('motivo_napex', $projeto->motivo_napex) }}</textarea>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Enviar Parecer</button>
        </form>
    @else
        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-4">
            <tbody>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Número do Projeto</th>
                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                    <td class="bg-white p-4 border-b border-gray-300">
                        {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                    <td class="bg-white p-4 border-b border-gray-300">
                        {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
                </tr>
            </tbody>
        </table>
        @if ($projeto->status != 'aprovado')
            <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'napex']) }}#form-parecer-napex"
            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-6">
                Editar Parecer
            </a>
        @endif
    @endif
@endif

@if(auth()->user()->role === 'coordenador')
    <h2 class="text-xl font-bold text-[#251C57] mb-4">Parecer do NAPEx</h2>
    <table class="min-w-full w-full border border-gray-300 rounded-lg mb-6">
        <tbody>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left w-1/6">Número do Projeto</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->numero_projeto ?? '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                </td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Data de Encaminhamento</th>
                <td class="bg-white p-4 border-b border-gray-300">
                    {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '--' }}
                </td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Aprovação</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_napex ? ucfirst($projeto->aprovado_napex) : '--' }}</td>
            </tr>
            <tr>
                <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_napex ?? '--' }}</td>
            </tr>
        </tbody>
    </table>

    <h2 class="text-xl font-bold text-[#251C57] mb-2">Parecer do Coordenador de Curso</h2>
    @if(request('editar') === 'coordenador' || (is_null($projeto->aprovado_coordenador) && auth()->user()->can('approveByCoordinator', $projeto)))
        <form id="form-parecer-coordenador" method="POST" action="{{ route('projetos.avaliar.coordenador', $projeto->id) }}" class="mb-10">
            @csrf
            <label>Aprovação</label>
            <select name="aprovado_coordenador" class="w-full border-gray-300 rounded-md mb-4">
                <option value="">Selecione</option>
                <option value="sim" {{ $projeto->aprovado_coordenador == 'sim' ? 'selected' : '' }}>Sim</option>
                <option value="nao" {{ $projeto->aprovado_coordenador == 'nao' ? 'selected' : '' }}>Não</option>
            </select>
            <label>Motivo</label>
            <textarea name="motivo_coordenador" class="w-full border-gray-300 rounded-md mb-4">{{ old('motivo_coordenador', $projeto->motivo_coordenador) }}</textarea>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Enviar Parecer</button>
        </form>
    @else
        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-4">
           <tbody>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Data de Recebimento</th>
                    <td class="bg-white p-4 border-b border-gray-300">
                        {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '--' }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Aprovação</th>
                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->aprovado_coordenador ? ucfirst($projeto->aprovado_coordenador) : '--' }}</td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Motivo</th>
                    <td class="bg-white p-4 border-b border-gray-300">{{ $projeto->motivo_coordenador ?? '--' }}</td>
                </tr>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left">Data do Parecer</th>
                    <td class="bg-white p-4 border-b border-gray-300">
                        {{ $projeto->data_parecer_coordenador ? \Carbon\Carbon::parse($projeto->data_parecer_coordenador)->format('d/m/Y') : '--' }}
                    </td>
                </tr>
            </tbody>
        </table>
        @if($projeto->status != 'aprovado')
            <a href="{{ route('projetos.show', ['id' => $projeto->id, 'editar' => 'coordenador']) }}#form-parecer-coordenador"
            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded inline-block mb-10">
                Editar Parecer
            </a>
        @endif
    @endif
@endif


@if ($projeto->rejeicoes->count() > 0)
    <h1 class="text-2xl font-bold text-[#251C57] text-center mb-8">Histórico de Rejeições</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full w-full border border-gray-300 rounded-lg mb-10">
            <thead>
                <tr>
                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Data da Rejeição</th>
                    <th class="bg-[#251C57] text-white p-4 text-left w-1/2">Motivo</th>
                    <th class="bg-[#251C57] text-white p-4 text-left w-1/4">Responsável</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projeto->rejeicoes as $rejeicao)
                    <tr>
                        <td class="bg-white p-4 border-b border-gray-300">
                            {{ \Carbon\Carbon::parse($rejeicao->data_rejeicao)->format('d/m/Y') }}
                        </td>
                        <td class="bg-white p-4 border-b border-gray-300">
                            {{ $rejeicao->motivo }}
                        </td>
                        <td class="bg-white p-4 border-b border-gray-300">
                            {{ ucfirst($rejeicao->autor) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif