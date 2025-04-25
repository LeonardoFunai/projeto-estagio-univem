@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/index.css') }}">

<h1>Propostas de Projeto Extensionista  Curricularização da Extensão</h1>

@if (session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif




<div style="margin-top: 20px;">
    <button id="btn-filtro" class="btn btn-primary">🔍 Filtrar</button>
    <a href="{{ route('projetos.index') }}" class="limpar-btn">Limpar Filtros</a>
</div>

<!-- Filtro com largura limitada e centralizado -->
<div id="filtro-box" style="display: flex; max-width: 1500px;">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('projetos.index') }}" class="filtro-form">
        <div>
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" value="{{ request('titulo') }}">
        </div>

        <div>
            <label for="periodo">Período:</label>
            <input type="text" name="periodo" value="{{ request('periodo') }}">
        </div>

        <div>
            <label for="data_inicio">Data de Início (mínima):</label>
            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}">
        </div>

        <div>
            <label for="data_fim">Data de Fim (máxima):</label>
            <input type="date" name="data_fim" value="{{ request('data_fim') }}">
        </div>

        <div>
            <label for="carga_min">Carga Mínima:</label>
            <input type="number" name="carga_min" value="{{ request('carga_min') }}">
        </div>

        <div>
            <label for="carga_max">Carga Máxima:</label>
            <input type="number" name="carga_max" value="{{ request('carga_max') }}">
        </div>

        <div>
            <label for="status">Status:</label>
            <select name="status">
                <option value="">-- Todos --</option>
                <option value="editando" {{ request('status') === 'editando' ? 'selected' : '' }}>Editando</option>
                <option value="entregue" {{ request('status') === 'entregue' ? 'selected' : '' }}>Entregue</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-success">Pesquisar</button>
        </div>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Título</th>
            <th>Período</th>
            <th>Data de Início</th>
            <th>Data de Fim</th>
            <th>Carga Horária</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projetos as $projeto)
            <tr>
                <td>{{ $projeto->titulo }}</td>
                <td>{{ $projeto->periodo }}</td>
                <td>{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>
                <td>{{ $projeto->atividades->sum('carga_horaria') ?? 0 }}h</td>
                <td>{{ ucfirst($projeto->status) }}</td>
                <td>
                <a href="{{ route('projetos.show', $projeto->id) }}" class="btn-link-azul">Visualizar</a>
                @if ($projeto->status !== 'entregue')|
                    <a href="{{ route('projetos.edit', $projeto->id) }}" class="btn-link-azul">Editar</a>
                @endif
                    |
                    <form 
                        action="{{ route('projetos.destroy', $projeto->id) }}" 
                        method="POST" 
                        onsubmit="return confirm('Tem certeza que deseja apagar este projeto?');"
                        style="display:inline; padding:0; margin:0; border:0;"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none; border:none; padding:0; margin:0; color:red; font-weight:bold; font-size:0.95rem; cursor:pointer;">
                            Apagar
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    const btnFiltro = document.getElementById('btn-filtro');
    const filtroBox = document.getElementById('filtro-box');
    btnFiltro.addEventListener('click', () => {
        filtroBox.style.display = filtroBox.style.display === 'none' ? 'block' : 'none';
    });
</script>

@endsection
