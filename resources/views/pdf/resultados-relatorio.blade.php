@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Resultados - {{ $resultado->projeto->titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; line-height: 1.4; }
        @page { margin: 100px 40px 80px 40px; }
        header { position: fixed; top: -80px; left: 0; right: 0; height: 70px; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 60px; font-size: 9px; color: #002c74; border-top: 1px solid #000; padding: 5px 20px; text-align: center; }
        footer p { margin: 1px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .no-border, .no-border td, .no-border th { border: none !important; }
        .header-logo { height: 50px; }
        .main-title { text-align: center; font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: bold; margin-top: 20px; margin-bottom: 5px; }
        .content-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; width: 300px; }
    </style>
</head>
<body>
    <header>
        <table class="no-border">
            <tr>
                <td style="width: 80px;"><img src="{{ public_path('img/site/logo-pdf.png') }}" class="header-logo"></td>
                <td style="text-align: left;">
                    <strong style="font-size: 10px;">MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”</strong><br>
                    <span style="font-size: 9px;">Centro Universitário Eurípedes de Marília</span>
                </td>
            </tr>
        </table>
        <div style="width: 100%; margin-top: 5px; border-bottom: 1px solid #000;"></div>
    </header>

    <footer>
        <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho, 529 - CEP 17525-901 - Marília/SP</p>
        <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94 - Telefone: (14) 2105-0800 - univem.edu.br</p>
    </footer>

    <h2 class="main-title">RELATÓRIO DE MENSURAÇÃO DE RESULTADOS<br><small style="font-weight: normal;">CURRICULARIZAÇÃO DA EXTENSÃO</small></h2>

    <div class="content-box">
        <h3 class="section-title">Identificação</h3>
        <p><strong>Título:</strong> {{ $resultado->projeto->titulo }}</p>
        <p><strong>Período:</strong> {{ $resultado->projeto->periodo }}</p>
        <p><strong>Professor(es) envolvidos:</strong> {{ $resultado->projeto->professores->pluck('nome')->implode(', ') }}</p>
    </div>

    <div class="content-box">
        <h3 class="section-title">Alunos Envolvidos</h3>
        <table>
            <thead><tr><th>Nome do Aluno</th><th>RA</th><th>Curso</th></tr></thead>
            <tbody>
                @foreach($resultado->projeto->alunos as $aluno)
                <tr><td>{{ $aluno->nome }}</td><td>{{ $aluno->ra }}</td><td>{{ $aluno->curso->nome }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($resultado->parceiro_organizacao)
    <div class="content-box">
        <h3 class="section-title">Parcerias - Organizações Envolvidas</h3>
        <p><strong>Organização:</strong> {{ $resultado->parceiro_organizacao }}</p>
        <p><strong>Endereço:</strong> {{ $resultado->parceiro_endereco }}</p>
        <p><strong>CNPJ:</strong> {{ $resultado->parceiro_cnpj }}</p>
        <p><strong>Nome do responsável:</strong> {{ $resultado->parceiro_responsavel }}</p>
        <p><strong>Tipo de participação:</strong> {{ $resultado->parceiro_tipo_participacao }}</p>
    </div>
    @endif

    <div class="content-box">
        <h3 class="section-title">Pessoas da Comunidade Externa</h3>
        <p>{{ $resultado->comunidade_externa ?? 'Nenhuma informação fornecida.' }}</p>
    </div>

    <div class="content-box">
        <h3 class="section-title">Atividades Desenvolvidas no período</h3>
        <p style="white-space: pre-wrap;">{{ $resultado->atividades_desenvolvidas }}</p>
    </div>

    <div class="content-box">
        <h3 class="section-title">Anexos</h3>
        <p><strong>Descrição:</strong> {{ $resultado->anexos_descricao ?? 'Nenhuma descrição fornecida.' }}</p>
        @if($resultado->anexos->isNotEmpty())
            <p><strong>Arquivos:</strong></p>
            <ul>
                @foreach($resultado->anexos as $anexo)
                    <li>{{ $anexo->nome_original }}</li>
                @endforeach
            </ul>
        @else
            <p>Nenhum arquivo anexado.</p>
        @endif
    </div>

</body>
</html>