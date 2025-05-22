@php use Illuminate\Support\Str; @endphp
@php
    use Carbon\Carbon;

    // Ajuste aqui se o formato estiver como 'Y-m-d'
    $inicio = Carbon::createFromFormat('Y-m-d', $projeto->data_inicio);
    $fim = Carbon::createFromFormat('Y-m-d', $projeto->data_fim);

    $meses = [];

    while ($inicio <= $fim) {
        $meses[] = $inicio->format('F');
        $inicio->addMonth();
    }

    $traducaoMeses = [
        'January' => 'Janeiro',
        'February' => 'Fevereiro',
        'March' => 'Março',
        'April' => 'Abril',
        'May' => 'Maio',
        'June' => 'Junho',
        'July' => 'Julho',
        'August' => 'Agosto',
        'September' => 'Setembro',
        'October' => 'Outubro',
        'November' => 'Novembro',
        'December' => 'Dezembro',
    ];
@endphp


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
            @page {
                margin-top: 80px;
                margin-bottom: 80px;
            }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
            white-space: pre-wrap;
            word-wrap: break-word;
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

        .section {
            margin-bottom: 2px;
            padding-top: 2px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1px;
            font-size: 11px;
            line-height: 1.2;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
        }

        .no-border td {
            border: none;
        }

        /* NOVO: remove bordas apenas do header */
        .no-border-header,
        .no-border-header td,
        .no-border-header th {
            border: none !important;
        }
        .title {
            margin-top: -70px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: -140px;
            padding: -10px
        }

    </style>

</head>
<body>


<header>
    <table width="100%" class="no-border-header">
        <tr>
            <td style="width: 70px;">
                <img src="{{ public_path('img/site/logo-pdf.png') }}" height="50">
            </td>
            <td style="text-align: left; padding-top: 30px;">
                <strong style="font-size: 10px;">MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”</strong>
            </td>
        </tr>
    </table>
</header>

<div class="title">
    PROPOSTA DE ATIVIDADE EXTENSIONISTA CURRICULARIZAÇÃO DA EXTENSÃO Resolução CNE/CES Nº 7 de 18/12/2018
    <p style="margin: 0px; font-weight: bold; font-size: 12px;">
    Proposta Nº {{ $projeto->numero_projeto ?? '_____' }} /2025
</p>
</div>





<table>
    <thead>
        <tr>
            <th colspan="3" style="text-align: center; font-size: 13px;">IDENTIFICAÇÃO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3" style="max-width: 1050px;"><strong>Título:</strong> {{ $projeto->titulo }}</td>
        </tr>
        <tr>
            <td colspan="3" style="max-width: 1050px;"><strong>Período:</strong> {{ $projeto->periodo }}</td>
        </tr>
        <tr>
            <td colspan="3" style="max-width: 1050px;"><strong>Professor(es) envolvidos:</strong> {{ $projeto->professores->pluck('nome')->implode(', ') }}</td>
        </tr>

        <tr>
            <td colspan="3" style="text-align: center;"><strong>Alunos envolvidos:</strong></td>
        </tr>
        <tr>
            <th style="max-width: 120px;">Nome Completo</th>
            <th style="max-width: 120px;">R.A</th>
            <th style="max-width: 150px;">Curso</th>
        </tr>
        @foreach ($projeto->alunos as $aluno)
        <tr>
            <td style="max-width: 120px;">{{ $aluno->nome }}</td>
            <td style="max-width: 120px;">{{ $aluno->ra }}</td>
            <td style="max-width: 120px;">{{ $aluno->curso }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="3" style="max-width: 1050px;"><strong>Público Alvo:</strong> {{ $projeto->publico_alvo }}</td>
        </tr>
        <tr>
            <td colspan="3" style="max-width: 1050px;"><strong>Período de realização:</strong> {{ $projeto->data_inicio }} a {{ $projeto->data_fim }}</td>
        </tr>
    </tbody>
</table>



<table>
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; font-size: 13px;">DESCRIÇÃO DO PROJETO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2"><strong>1 - Introdução:</strong><br>{{ $projeto->introducao }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>2 - Objetivo Geral:</strong><br>{{ $projeto->objetivo_geral }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>3 - Justificativa:</strong><br>{{ $projeto->justificativa }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>4 - Metodologia:</strong><br>{{ $projeto->metodologia }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>5 - Atividades a serem desenvolvidas:</strong><br>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-top: -25px;">
                    <thead>
                        <tr>
                            <th style="width: 48%;">O que fazer?</th>
                            <th style="width: 48%;">Como fazer?</th>
                            <th style="width: 4%;">Carga horária</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projeto->atividades as $a)
                        <tr>
                            <td style="max-width: 200px;">{{ $a->o_que_fazer }}</td>
                            <td style="max-width: 200px;">{{ $a->como_fazer }}</td>
                            <td style="max-width: 200px;">{{ $a->carga_horaria }}h</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>6 - Cronograma:</strong><br>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th style="max-width: 50px;">Atividade</th>
                            @foreach ($meses as $m)
                                <th>{{ $traducaoMeses[$m] ?? $m }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($projeto->cronogramas as $c)
                            <tr>
                                <td style="max-width: 40px;">{{ $c->atividade }}</td>
                                @foreach ($meses as $m)
                                    <td>{{ $c->mes == $traducaoMeses[$m] ? '✔' : '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2"><strong>7 - Recursos Necessários:</strong><br>{{ $projeto->recursos }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>8 - Resultados Esperados:</strong><br>{{ $projeto->resultados_esperados }}</td>
        </tr>
    </tbody>
</table>

</body>
</html>
