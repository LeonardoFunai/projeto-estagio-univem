<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Propostas Extensionistas</title>
    <style>
        * { font-family: Arial, sans-serif; font-size: 11px; }
        body { margin: 0; padding: 0; }

        .header {
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: fixed;
            top: 0;
            padding: 10px 0;
        }

        .header img {
            height: 60px;
        }

        .header-text {
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 95%;
            border-top: 1px solid #000;
            padding: 10px 20px;
            font-size: 9px;
            color: #002c74;
        }

        .footer p {
            margin: 2px 0;
        }

        .title {
            margin-top: 30px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .gerado-por {
            margin: 10px 40px;
            text-align: right;
            font-size: 11px;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            margin-bottom: 80px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
        table, tr, td {
            border: none;
        }

    </style>
</head>
<body>
    <!-- Cabeçalho e logo -->
    <div class="header">
        <table width="100%" style="vertical-align: middle; border: none;">
            <tr>
                <td style="width: 60px;">
                    <img src="{{ public_path('img/site/logo-pdf.png') }}" style="height: 50px;">
                </td>
                <td style="text-align: left;">
                    <div style="font-size: 11px; font-weight: bold;">
                        MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <br>
    <div style="width: 95%; margin: 0 auto; border-bottom: 1px solid #000; margin-top: 80px;"></div>

    <!-- Título -->
    <div class="title">
        Relatório de Propostas de atividade Extensionista Curricularização da Extensão

    </div>
    <div style="text-align: center;">
        <p>Relatório gerado por: <strong>{{ $usuario }}</strong> em {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    @if (!empty($filtros))
        <div style="text-align: center; margin-top: 5px;">
            <p><strong>Filtros aplicados:</strong></p>

            @if (!empty($filtros['data_inicio_de']) && !empty($filtros['data_inicio_ate']))
                <p>Data de Início: {{ $filtros['data_inicio_de'] }} até {{ $filtros['data_inicio_ate'] }}</p>
            @endif

            @if (!empty($filtros['data_fim_de']) && !empty($filtros['data_fim_ate']))
                <p>Data de Fim: {{ $filtros['data_fim_de'] }} até {{ $filtros['data_fim_ate'] }}</p>
            @endif

            @if (!empty($filtros['carga_min']) || !empty($filtros['carga_max']))
                <p>Carga Horária: {{ $filtros['carga_min'] ?? '0' }}h até {{ $filtros['carga_max'] ?? '∞' }}h</p>
            @endif

            @if (!empty($filtros['status']) && $filtros['status'] !== '--')
                <p>Status: {{ ucfirst($filtros['status']) }}</p>
            @endif

            @if (!empty($filtros['aprovado_napex']) && $filtros['aprovado_napex'] !== '--')
                <p>Aprovação NAPEx: {{ ucfirst($filtros['aprovado_napex']) }}</p>
            @endif

            @if (!empty($filtros['aprovado_coordenador']) && $filtros['aprovado_coordenador'] !== '--')
                <p>Aprovação Coordenador: {{ ucfirst($filtros['aprovado_coordenador']) }}</p>
            @endif
        </div>
    @endif

    <div style="text-align: center; margin-top: 15px; font-size: 12px;">
        Total de propostas listadas: <strong>{{ count($projetos) }}</strong>
    </div>


    <!-- Tabela de projetos -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cadastrado por</th>
                <th>Título</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Carga Horária</th>
                <th>NAPEx</th>
                <th>Coord.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projetos as $index => $projeto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $projeto->user->name ?? '-' }}</td>
                    <td>{{ $projeto->titulo }}</td>
                    <td>{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>
                    <td>{{ $projeto->carga_horaria }} h</td>
                    <td>{{ ucfirst($projeto->aprovado_napex ?? '-') }}</td>
                    <td>{{ ucfirst($projeto->aprovado_coordenador ?? '-') }}</td>
                    <td>{{ ucfirst($projeto->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <!-- Rodapé textual -->
    <div class="footer">
        <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho,
         529 - CEP 17525-901 - Marília/SP</p>
        <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94
         - Telefone: (14) 2105-0800 - univem.edu.br</p>

    </div>
</body>
</html>
