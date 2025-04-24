<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propostas de Projeto Extensionista Curricularização da Extensão</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        #filtro-box {
            display: none;
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .filtro-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }

        .filtro-form div {
            display: flex;
            flex-direction: column;
        }

        .filtro-form input,
        .filtro-form select {
            padding: 6px;
            max-width: 250px;
        }

        .filtro-form button {
            margin-top: 24px;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: #1d4e89;
            color: white;
        }

        .limpar-btn {
            margin-left: 10px;
            background:rgb(92, 92, 92);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
        }

        #btn-filtro {
            margin: 20px 0 0 0;
            background: #1d4e89;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        #btn-filtro:hover {
            background: #163a66;
        }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; align-items: center;">
            <img src="{{ asset('img/site/univem-logo.jpg') }}" alt="Logo Univem" style="height: 50px; margin-right: 15px;">
            <div>
                <h1 style="margin: 0;">Univem - Centro Universitário Eurípedes de Marília</h1>
            </div>
        </div>
    </header>

    <h1>Propostas de Projeto Extensionista <br> Curricularização da Extensão</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <a href="{{ route('projetos.create') }}">Cadastrar Novo Projeto</a>

    <div style="margin-top: 20px;">
        <button id="btn-filtro">🔍 Filtrar</button>
        <a href="{{ route('projetos.index') }}" class="limpar-btn">Limpar Filtros</a>
    </div>

    <div id="filtro-box">
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
                <button type="submit">Pesquisar</button>
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
                        <a href="{{ route('projetos.show', $projeto->id) }}">Visualizar</a>
                        @if ($projeto->status !== 'entregue')
                            | <a href="{{ route('projetos.edit', $projeto->id) }}">Editar</a>
                        @endif
                        |
                        <form action="{{ route('projetos.destroy', $projeto->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja apagar este projeto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Apagar</button>
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
</body>
</html>
