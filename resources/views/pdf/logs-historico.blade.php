<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Histórico do Projeto - {{ $projeto->titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 4px 0; color: #555; font-size: 11px;}
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        thead { background-color: #f2f2f2; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 12px;
            color: #fff;
            border: 1px solid #555;
        }
        .badge-proposta { background-color: #3b82f6; }
        .badge-relatorio { background-color: #8b5cf6; }
        .group-row { border-left: 3px solid #3b82f6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Histórico do Projeto de Extensão</h1>
        <p><strong>Projeto:</strong> {{ $projeto->titulo }}</p>
        <p><strong>Autor:</strong> {{ $projeto->user->name }}</p>
        <p><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Data</th>
                <th style="width: 15%;">Usuário</th>
                <th style="width: 12%;">Origem</th>
                <th style="width: 18%;">Ação</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            @if ($logs->isNotEmpty())
                @foreach ($logs->groupBy('batch_id') as $batchId => $batch)
                    @php
                        $isGroup = !empty($batchId) && $batch->count() > 1;
                    @endphp

                    @foreach ($batch as $log)
                        @if ($loop->first)
                            <tr @if($isGroup) class="group-row" @endif>
                                <td @if($isGroup) rowspan="{{ $batch->count() }}" @endif>
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td @if($isGroup) rowspan="{{ $batch->count() }}" @endif>
                                    {{ $log->user->name ?? 'Sistema' }}
                                </td>
                                <td>
                                    @if (str_contains($log->loggable_type, 'Projeto'))
                                        <span class="badge badge-proposta">Proposta</span>
                                    @elseif (str_contains($log->loggable_type, 'Resultado'))
                                        <span class="badge badge-relatorio">Relatório</span>
                                    @endif
                                </td>
                                <td>{{ $log->acao }}</td>
                                <td>{{ $log->descricao }}</td>
                            </tr>
                        @else
                            <tr class="group-row">
                                {{-- As colunas Data e Usuário são omitidas aqui por causa do rowspan --}}
                                <td>
                                    @if (str_contains($log->loggable_type, 'Projeto'))
                                        <span class="badge badge-proposta">Proposta</span>
                                    @elseif (str_contains($log->loggable_type, 'Resultado'))
                                        <span class="badge badge-relatorio">Relatório</span>
                                    @endif
                                </td>
                                <td>{{ $log->acao }}</td>
                                <td>{{ $log->descricao }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhum histórico encontrado.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>