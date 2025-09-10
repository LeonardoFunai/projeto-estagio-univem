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
        .main-title { text-align: center; font-size: 14px; font-weight: bold; margin-top: 10px; margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: bold; margin-top: 10px; margin-bottom: 5px; }
        .content-box { border: 1px solid #ccc; padding: 5px; margin-bottom:5px;  }
        .signature-line { border-top: 1px solid #000; margin-top: 20px; width: 300px; }
        .signature-container { margin-top: 60px; page-break-inside: avoid; }
        .signature-container h3 { text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 12px; }
        .signature-table td { padding: 8px; vertical-align: bottom; height: 45px; }
        .validation-container { margin-top: 40px; page-break-inside: avoid; }
        .validation-container h3 { text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 12px; }
        .validation-container td { height: 60px; text-align: center; vertical-align: middle; }
        .final-date { text-align: center; margin-top: 40px; }

        .anexo-imagem {
        max-width: 100%;   
        height: auto;      
        margin-top: 10px; 
        margin-bottom: 15px; 
    }
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
        <div style="width: 100%; margin-top: 2px; border-bottom: 1px solid #000;"></div>
    </header>

    <footer>
        <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho, 529 - CEP 17525-901 - Marília/SP</p>
        <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94 - Telefone: (14) 2105-0800 - univem.edu.br</p>
    </footer>

    <h2 class="main-title">RELATÓRIO DE MENSURAÇÃO DE RESULTADOS<br><small style="font-weight: normal;">CURRICULARIZAÇÃO DA EXTENSÃO <br> Resolução CNE/CES Nº 7 de 18/12/2018</small></h2>

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
            {{-- Separa os anexos entre imagens e outros arquivos --}}
            @php
                $imagens = $resultado->anexos->filter(fn($anexo) => Str::startsWith($anexo->mime_type, 'image/'));
                $outrosArquivos = $resultado->anexos->filter(fn($anexo) => !Str::startsWith($anexo->mime_type, 'image/'));
            @endphp

            {{-- Mostra as imagens, se houver --}}
            @if($imagens->isNotEmpty())
                <h4 style="font-weight: bold; margin-top: 15px;">Imagens Anexadas:</h4>
                @foreach($imagens as $anexo)
                    {{-- Usa o caminho absoluto do arquivo para o PDF conseguir renderizar --}}
                    <img src="{{ storage_path('app/public/' . $anexo->path) }}" class="anexo-imagem">
                @endforeach
            @endif

            {{-- Lista os outros arquivos, se houver --}}
            @if($outrosArquivos->isNotEmpty())
                <h4 style="font-weight: bold; margin-top: 15px;">Outros Arquivos:</h4>
                <ul>
                    @foreach($outrosArquivos as $anexo)
                        <li>{{ $anexo->nome_original }}</li>
                    @endforeach
                </ul>
            @endif

        @else
            <p>Nenhum arquivo anexado.</p>
        @endif
</div>

    @if($resultado->status === 'aprovado')
        @php
            $totalHoras = $resultado->projeto->atividades->sum('carga_horaria');
            $alunoRepresentante = $resultado->projeto->user->name ?? 'Não definido';
            $professorOrientador = $resultado->projeto->professores->first()->nome ?? 'Não definido';
            $responsavelOrganizacao = $resultado->parceiro_responsavel ?? 'Não aplicável';
        @endphp

        <div class="signature-container">
            <h3>Assinaturas e Datas</h3>
            <table class="signature-table">
                <tbody>
                    <tr><td><strong>Aluno representante do grupo participante:</strong> {{ $alunoRepresentante }}</td></tr>
                    <tr><td><strong>Professor orientador:</strong> {{ $professorOrientador }}</td></tr>
                    <tr><td><strong>Responsável da Organização:</strong> se tiver pode anexar uma declaração</td></tr>
                    <tr><td><strong>Coordenação do NAPEX</strong></td></tr>
                </tbody>
            </table>
        </div>

        <div class="validation-container">
            <h3>Validação das horas pelo Coordenador do Curso</h3>
            <table class="signature-table">
                <tbody>
                    <tr>
                        <td>Horas atribuída ao projeto de Extensão do {{ $resultado->projeto->periodo }}: <strong>{{ $totalHoras }} horas</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="final-date">
            <p>Marília, {{ $resultado->updated_at->locale('pt_BR')->isoFormat('DD [de] MMMM [de] YYYY') }}.</p>
        </div>
    @endif

</body>
</html>