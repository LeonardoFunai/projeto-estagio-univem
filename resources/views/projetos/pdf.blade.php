@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 120px 40px 80px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            white-space: pre-line;
            word-wrap: break-word;
white-space: pre-wrap;

        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
            word-wrap: break-word;
white-space: pre-wrap;

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
            margin-bottom: 15px;
            white-space: pre-line;
            word-wrap: break-word;
white-space: pre-wrap;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
            line-height: 1.2;
            word-wrap: break-word;
white-space: pre-wrap;

        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            white-space: pre-line;
            word-wrap: break-word;
white-space: pre-wrap;

        }

        th {
            background-color: #f0f0f0;
        }

        .no-border td {
            border: none;
        }
    </style>
</head>
<body>

<header>
    <table width="100%">
        <tr>
            <td style="width: 70px;">
                <img src="{{ public_path('img/site/logo-pdf.png') }}" height="60">
            </td>
            <td style="text-align: left;">
                <strong style="font-size: 12px;">MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”</strong><br>
                <span style="font-size: 10px;">Centro Universitário Eurípedes de Marília – UNIVEM</span>
            </td>
        </tr>
    </table>
</header>

<footer>
    <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho, 529 - CEP 17525-901 - Marília/SP</p>
    <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94 - Telefone: (14) 2105-0800 - univem.edu.br</p>
</footer>

<h2 style="text-align: center;">Proposta de Projeto Extensionista - Curricularização da Extensão</h2>

<div class="section"><strong>Título:</strong> {{ $projeto->titulo }}</div>
<div class="section"><strong>Período:</strong> {{ $projeto->periodo }}</div>
<div class="section"><strong>Professor(es) envolvidos:</strong> {{ $projeto->professores->pluck('nome')->implode(', ') }}</div>

<div class="section">
    <strong>Alunos envolvidos:</strong>
    <table>
        <thead>
            <tr>
                <th>Nome Completo</th>
                <th>R.A</th>
                <th>Curso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projeto->alunos as $aluno)
            <tr>
                <td>{{ $aluno->nome }}</td>
                <td>{{ $aluno->ra }}</td>
                <td>{{ $aluno->curso }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section"><strong>Público Alvo:</strong> {{ $projeto->publico_alvo }}</div>
<div class="section"><strong>Período de realização:</strong> {{ $projeto->data_inicio }} a {{ $projeto->data_fim }}</div>

<div class="section"><strong>1 - Introdução:</strong><br>{{ $projeto->introducao }}</div>
<div class="section"><strong>2 - Objetivo Geral:</strong><br>{{ $projeto->objetivo_geral }}</div>
<div class="section"><strong>3 - Justificativa:</strong><br>{{ $projeto->justificativa }}</div>
<div class="section"><strong>4 - Metodologia:</strong><br>{{ $projeto->metodologia }}</div>

<div class="section">
    <strong>5 - Atividades a serem desenvolvidas:</strong>
    <table>
        <thead>
            <tr>
                <th>O que fazer?</th>
                <th>Como fazer?</th>
                <th>Carga horária</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projeto->atividades as $a)
            <tr>
                <td>{{ $a->o_que_fazer }}</td>
                <td>{{ $a->como_fazer }}</td>
                <td>{{ $a->carga_horaria }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <strong>Cronograma:</strong>
    <table>
        <thead>
            <tr>
                <th>Atividade</th>
                <th>Agosto</th>
                <th>Setembro</th>
                <th>Outubro</th>
                <th>Novembro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projeto->cronogramas as $c)
            <tr>
                <td>{{ $c->atividade }}</td>
                <td>{{ $c->mes == 'Agosto' ? '✔' : '' }}</td>
                <td>{{ $c->mes == 'Setembro' ? '✔' : '' }}</td>
                <td>{{ $c->mes == 'Outubro' ? '✔' : '' }}</td>
                <td>{{ $c->mes == 'Novembro' ? '✔' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section"><strong>Recursos Necessários:</strong><br>{{ $projeto->recursos }}</div>
<div class="section"><strong>Resultados Esperados:</strong><br>{{ $projeto->resultados_esperados }}</div>

</body>
</html>
