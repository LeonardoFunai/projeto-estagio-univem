    @php use Illuminate\Support\Str; @endphp
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Relatório de Propostas Extensionistas</title>
        <style>
            @page {
                margin-top: 120px;
                margin-bottom: 80px;
            }
            tbody tr {
                border-bottom: 1px solid #999;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
                margin: 0;
                padding: 0;
            }

            header {
                position: fixed;
                top: -100px;
                left: 0;
                right: 0;
                height: 100px;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0;
                right: 0;
                height: 60px;
                font-size: 9px;
                color: #002c74;
                border-top: 1px solid #000;
                padding: 10px 20px;
            }

            .header img {
                height: 60px;
            }

            .header-text {
                font-size: 12px;
                font-weight: bold;
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

    <!-- Cabeçalho que será fixado no topo de cada página -->
    <header>
        <div class="header">
            <table width="100%" style="vertical-align: middle; border: none;">
                <tr style = 'border-bottom: none'>
                    <td style="width: 50px;">
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
    </header>
    <!-- Divisão do header -->
    <div style="width: 100%; margin: 0 auto; border-bottom: 1px solid #000;"></div>

    <!-- Rodapé fixado no final de cada página -->
    <footer>
        <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho,
        529 - CEP 17525-901 - Marília/SP</p>
        <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94
        - Telefone: (14) 2105-0800 - univem.edu.br</p>
    </footer>

    <!-- Conteúdo principal -->
    <div class="title">
        Relatório de Propostas de atividade Extensionista Curricularização da Extensão
    </div>

    <div style="text-align: center;">
        <p>Relatório gerado por: <strong>{{ auth()->user()->name; }}</strong> em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if (!empty($filtros))
        <div style="width: 90%; margin: 10px auto; border-bottom: 1px dashed #000;"></div>


        <div style="text-align: center; margin-top: 2px;">
            <p style="font-weight: bold; font-size: 11px;">Filtros aplicados:</p>

            <div style="text-align: left; margin: 0 auto; width: 90%;">
                @php
                    $campos = [];

                    if (!empty($filtros['cadastrado_por']))
                        $campos[] = "<strong>Cadastrado por:</strong> {$filtros['cadastrado_por']}";

                    if (!empty($filtros['titulo']))
                        $campos[] = "<strong>Título:</strong> {$filtros['titulo']}";

                    if (!empty($filtros['data_inicio_de']) && !empty($filtros['data_inicio_ate']))
                        $campos[] = "<strong>Data de Início:</strong> {$filtros['data_inicio_de']} até {$filtros['data_inicio_ate']}";

                    if (!empty($filtros['data_fim_de']) && !empty($filtros['data_fim_ate']))
                        $campos[] = "<strong>Data de Fim:</strong> {$filtros['data_fim_de']} até {$filtros['data_fim_ate']}";

                    if (!empty($filtros['carga_min']) || !empty($filtros['carga_max'])) {
                        $min = $filtros['carga_min'] ?? '0';
                        $max = $filtros['carga_max'] ?? '∞';
                        $campos[] = "<strong>Total de Horas:</strong> {$min}h até {$max}h";
                    }

                    if (!empty($filtros['status']) && $filtros['status'] !== '--')
                        $campos[] = "<strong>Status:</strong> " . ucfirst($filtros['status']);

                    if (!empty($filtros['aprovado_napex']) && $filtros['aprovado_napex'] !== '--')
                        $campos[] = "<strong>Aprovação NAPEx:</strong> " . ucfirst($filtros['aprovado_napex']);

                    if (!empty($filtros['aprovado_coordenador']) && $filtros['aprovado_coordenador'] !== '--')
                        $campos[] = "<strong>Aprovação Coordenador:</strong> " . ucfirst($filtros['aprovado_coordenador']);
                @endphp

                @foreach(array_chunk($campos, 3) as $linha)
                    <div style="width: 100%; margin-bottom: 4px;">
                        @foreach($linha as $coluna)
                            <div style="display: inline-block; width: 32%; font-size: 11px;">
                                {!! $coluna !!}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div style="width: 90%; margin: 10px auto; border-bottom: 1px dashed #000;"></div>
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
                    <td style="max-width: 200px; word-wrap: break-word; white-space: pre-line;">
                        {{ $projeto->titulo }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td>
                    <td>{{ $projeto->atividades->sum('carga_horaria') ?? 0 }}hh</td>
                    <td>{{ ucfirst($projeto->aprovado_napex ?? 'pendente') }}</td>
                    <td>{{ ucfirst($projeto->aprovado_coordenador ?? 'pendente') }}</td>
                    <td>{{ ucfirst($projeto->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    </body>
    </html>
