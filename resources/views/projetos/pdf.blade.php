@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    // Garante valores padrão para as datas do projeto se estiverem nulas para evitar erros com Carbon.
    // O formato 'Y-m-d' é esperado. Ajuste se o formato de entrada for diferente.
    $dataInicioInput = $projeto->data_inicio ?? '1970-01-01';
    $dataFimInput = $projeto->data_fim ?? '1970-01-01';

    // Tenta criar datas Carbon. Se o formato for inválido, usa datas padrão para evitar crash.
    try {
        $inicio = Carbon::createFromFormat('Y-m-d', $dataInicioInput)->startOfDay();
        $fim = Carbon::createFromFormat('Y-m-d', $dataFimInput)->endOfDay();
    } catch (\Carbon\Exceptions\InvalidFormatException $e) {
        // Em caso de formato inválido, define datas seguras para evitar loops infinitos ou erros.
        $inicio = Carbon::createFromFormat('Y-m-d', '1970-01-01')->startOfDay();
        $fim = Carbon::createFromFormat('Y-m-d', '1970-01-01')->endOfDay();
        // Logar o erro $e seria uma boa prática em um ambiente de produção.
    }

    $meses = [];
    // Gera a lista de meses apenas se a data de início for anterior ou igual à data de fim.
    if ($inicio->lte($fim)) {
        $currentMonth = $inicio->copy();
        while ($currentMonth->lte($fim)) {
            $meses[] = $currentMonth->format('F'); // 'F' para nome completo do mês (e.g., January)
            $currentMonth->addMonth();
        }
        $meses = array_unique($meses); // Garante que não haja meses duplicados se o período for muito curto
    }

    // Array para tradução dos nomes dos meses para o português.
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

    // Função helper para formatar datas no padrão "dd de MMMM de yyyy" ou mostrar placeholders.
    // Carbon::setLocale('pt_BR') deve ser configurado globalmente (ex: AppServiceProvider) para translatedFormat funcionar corretamente.
    // Se não, 'F' produzirá o mês em inglês.
    function formatarDataPortugues($data) {
        if ($data) {
            try {
                // Garante que 'pt_BR' esteja disponível e configurado para Carbon
                // Se Carbon::setLocale('pt_BR') não foi chamado globalmente, pode ser necessário fazer aqui
                // ou usar $traducaoMeses para traduzir o mês manualmente.
                return Carbon::parse($data)->translatedFormat('d \d\e F \d\e Y');
            } catch (Exception $e) {
                // Fallback para data simples se a tradução falhar
                return Carbon::parse($data)->format('d/m/Y');
            }
        }
        return '____ de _______________ de _______';
    }
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta de Atividade Extensionista - {{ $projeto->numero_projeto ?? 'N/A' }}</title>
    <style>
        /* Configurações globais da página para impressão/PDF.
          A margem superior é aumentada para acomodar o header fixo.
          A margem inferior é para o footer fixo.
        */
        @page {
            margin-top: 160px; /* Espaço reservado no topo da página para o header + pequena folga */
            margin-bottom: 80px; /* Espaço reservado na base da página para o footer */
            margin-left: 20px;
            margin-right: 20px;
        }

        /* Estilização base do corpo do documento */
        body {
            font-family: 'DejaVu Sans', sans-serif; /* DejaVu Sans é bom para caracteres UTF-8 em PDFs */
            font-size: 12px;
            margin: 0; /* Remove margens padrão do body */
            padding: 0; /* Remove paddings padrão do body */
            line-height: 1.4; /* Melhora a legibilidade do texto */
        }

        /* Estilização do header fixo */
        header {
            position: fixed; /* Fixa o header no topo de cada página */
            top: -140px; /* Puxa o header para dentro da área da margem superior definida em @page. 
                           O valor deve ser igual ao margin-top do @page para que o header comece no topo físico. */
            left: 0;
            right: 0;
            height: 90px; /* Altura total do header. O conteúdo dentro do header deve caber aqui. */
            background-color: #fff; /* Fundo branco para o header, caso haja algum elemento por baixo */
            padding-left: 20px; /* Alinha com margens da página */
            padding-right: 20px; /* Alinha com margens da página */
            /* border-bottom: 1px solid #ccc; */ /* Linha opcional para separar header do conteúdo */
        }

        /* Estilização do footer fixo */
        footer {
            position: fixed; /* Fixa o footer na base de cada página */
            bottom: -60px; /* Puxa o footer para dentro da área da margem inferior definida em @page */
            left: 0;
            right: 0;
            height: 60px; /* Altura do footer */
            font-size: 9px;
            color: #002c74;
            border-top: 1px solid #000;
            padding: 10px 20px; /* Padding interno do footer */
            background-color: #fff; /* Fundo branco para o footer */
            text-align: center; /* Centraliza o texto do footer */
        }
        footer p {
            margin: 2px 0; /* Espaçamento entre parágrafos no footer */
        }

        /* Container para o conteúdo principal. 
          A margem superior do @page já cria o espaço necessário para o header.
          Não é necessário margin-top aqui se o header estiver posicionado corretamente.
        */
        .content-wrapper {
            /* padding-top: 15px; */ /* Adiciona um respiro VISUAL entre o header e a primeira tabela.
                                       A margem do @page já afasta o fluxo do conteúdo. Esse padding é DENTRO do fluxo.
                                       Se o header tem 90px e está em top: -105px, e @page margin-top é 105px,
                                       o conteúdo começa 105px abaixo do topo físico. O header ocupa 0-90px.
                                       Então há 15px de espaço entre o fim do header (90px) e o início do conteúdo (105px).
                                       Este padding-top no content-wrapper seria ADICIONAL a esses 15px.
                                       Vamos omitir por enquanto, pois os 15px de folga já devem ser suficientes.
                                    */
        }

        /* Estilização padrão para tabelas */
        table {
            width: 100%;
            border-collapse: collapse; /* Remove espaços entre bordas de células */
            margin-bottom: 15px; /* Espaçamento após cada tabela principal */
            font-size: 11px;
            line-height: 1.3;
            /* Permite quebra de linha e quebra de palavras longas para evitar overflow */
            table-layout: fixed; /* Ajuda a controlar larguras de coluna de forma mais previsível */
        }

        /* Estilização para células de cabeçalho (th) e dados (td) */
        th, td {
            border: 1px solid #333; /* Bordas mais suaves */
            padding: 5px; /* Padding interno das células */
            text-align: left;
            vertical-align: top; /* Alinha conteúdo no topo da célula por padrão */
            word-wrap: break-word; /* Quebra palavras longas */
            hyphens: auto; /* Habilita hifenização se suportado e configurado no idioma */
        }

        /* Fundo para células de cabeçalho */
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Classe para tabelas sem bordas (usada no header) */
        .no-border-table,
        .no-border-table td,
        .no-border-table th {
            border: none !important;
        }

        /* Tabela para logo e texto de apoio no header */
        .header-logo-table {
            width: 100%;
            margin-bottom: 8px; /* Espaço entre a tabela do logo e o título principal do header */
        }
        .header-logo-table td {
            padding: 0; /* Reset padding for finer control */
            vertical-align: middle; /* Centraliza verticalmente o logo e texto */
        }
        .header-logo-table img {
            vertical-align: middle; /* Garante alinhamento da imagem */
        }


        /* Div para o título principal do header */
        .header-title-div {
            text-align: center;
            font-size: 12px; /* Reduzido para melhor encaixe */
            font-weight: bold;
            width: 100%;
            line-height: 1.3; /* Espaçamento entre linhas do título */
        }
        .header-title-div span {
            display: block; /* Cada span em uma nova linha */
        }
        .header-title-div .resolution-text {
            font-size: 10px; /* Fonte menor para a resolução e número da proposta */
            font-weight: normal; /* Normal weight for sub-lines */
        }


        /* Estilos para tabelas aninhadas (atividades, cronograma) */
        .nested-table {
            margin-top: 5px;
            margin-bottom: 0; /* Remove margem inferior se for a última coisa na célula pai */
            font-size: 10px; /* Fonte menor para tabelas aninhadas */
        }
        .nested-table th, .nested-table td {
            padding: 3px; /* Padding menor para células de tabelas aninhadas */
        }

        /* Estilos para seções de assinatura */
        .signature-section {
            padding-top: 10px;
            padding-bottom: 30px; /* Espaço para assinatura manual */
            /* border-bottom: 1px solid #000; */ /* Linha para assinatura, opcional */
            /* margin-bottom: 10px; */
        }
        .signature-section strong {
            display: block;
            margin-bottom: 30px; /* Espaço entre o texto e a área da assinatura */
        }

        /* Estilos para a seção de Pareceres */
        .parecer-checkbox {
            font-family: DejaVu Sans, sans-serif; /* Garante que 'x' seja renderizado corretamente */
            display: inline-block;
            width: 15px; /* Largura para o checkbox */
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <table class="no-border-table header-logo-table">
        <tr>
            <td style="width: 80px; text-align: left;">
                {{-- public_path() é um helper do Laravel que retorna o caminho absoluto para a pasta public.
                     É importante para geradores de PDF que precisam acessar o arquivo no sistema de arquivos. --}}
                <img src="{{ public_path('img/site/logo-pdf.png') }}" alt="Logo" height="50">
            </td>
            <td style="text-align: left; vertical-align: bottom; padding-left: 10px;">
                <strong style="font-size: 9px; display:block; line-height: 1.2;">
                    MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”
                </strong>
            </td>
        </tr>
    </table>
    <div class="header-title-div">
        {{-- Título dividido em spans para melhor controle de quebra de linha e estilização --}}
        <span>PROPOSTA DE ATIVIDADE EXTENSIONISTA CURRICULARIZAÇÃO DA EXTENSÃO</span>
        <span class="resolution-text">Resolução CNE/CES Nº 7 de 18/12/2018</span>
        <span class="resolution-text">Proposta Nº {{ $projeto->numero_projeto ?? '_____' }} / {{ $projeto->ano_proposta ?? Carbon::now()->year }}</span>
    </div>
</header>

<footer>
    <p><strong>Centro Universitário Eurípedes de Marília - Código e-MEC:3529</strong> - Av. Hygino Muzzi Filho, 529 - CEP 17525-901 - Marília/SP</p>
    <p>Mantido Pela Fundação de Ensino Eurípides Soares da Rocha - CNPJ: 52.059.573/0001-94 - Telefone: (14) 2105-0800 - univem.edu.br</p>
</footer>

<div class="content-wrapper">
    {{-- Tabela de Identificação --}}
    <table>
        <thead>
            <tr>
                <th colspan="3" style="text-align: center; font-size: 13px;">IDENTIFICAÇÃO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><strong>Título:</strong> {{ $projeto->titulo }}</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Período:</strong> {{ $projeto->periodo }}</td>
            </tr>
            <tr>
                <td colspan="3"><strong>Professor(es) envolvidos:</strong> {{ $projeto->professores->pluck('nome')->implode(', ') }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;"><strong>Alunos envolvidos:</strong></td>
            </tr>
            <tr>
                <th style="width: 40%;">Nome Completo</th>
                <th style="width: 20%;">R.A</th>
                <th style="width: 40%;">Curso</th>
            </tr>
            @forelse ($projeto->alunos as $aluno)
            <tr>
                <td>{{ $aluno->nome }}</td>
                <td>{{ $aluno->ra }}</td>
                <td>{{ $aluno->curso }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center;">Nenhum aluno envolvido informado.</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="3"><strong>Público Alvo da Atividade Extensionista:</strong> {{ $projeto->publico_alvo }}</td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Período da realização do projeto de Atividade Extensionista:</strong>
                    {{-- Formata as datas de início e fim do projeto --}}
                    {{ $inicio->year > 1970 ? $inicio->format('d/m/Y') : '__/__/____' }} a
                    {{ $fim->year > 1970 ? $fim->format('d/m/Y') : '__/__/____' }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Tabela de Descrição do Projeto --}}
    <table>
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; font-size: 13px;">DESCRIÇÃO DO PROJETO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2"><strong>1 - Introdução:</strong><br>{!! nl2br(e($projeto->introducao)) !!}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>2 - Objetivo Geral:</strong><br>{!! nl2br(e($projeto->objetivo_geral)) !!}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>3 - Justificativa:</strong><br>{!! nl2br(e($projeto->justificativa)) !!}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>4 - Metodologia:</strong><br>{!! nl2br(e($projeto->metodologia)) !!}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>5 - Atividades a serem desenvolvidas:</strong>
                    <table class="nested-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">O que fazer?</th>
                                <th style="width: 45%;">Como fazer?</th>
                                <th style="width: 10%; text-align:center;">Carga horária</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projeto->atividades as $a)
                            <tr>
                                <td>{{ $a->o_que_fazer }}</td>
                                <td>{{ $a->como_fazer }}</td>
                                <td style="text-align:center;">{{ $a->carga_horaria }}h</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" style="text-align: center;">Nenhuma atividade informada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>6 - Cronograma:</strong>
                    @if (!empty($meses))
                    <table class="nested-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Atividade</th>
                                {{-- Cabeçalhos dos meses, traduzidos --}}
                                @foreach ($meses as $m)
                                    <th style="text-align:center;">{{ $traducaoMeses[$m] ?? $m }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projeto->cronogramas as $c)
                            <tr>
                                <td>{{ $c->atividade }}</td>
                                {{-- Marcação ('✔') no mês correspondente à atividade do cronograma --}}
                                @foreach ($meses as $m)
                                    <td style="text-align:center;">{{ $c->mes == ($traducaoMeses[$m] ?? $m) ? '✔' : '' }}</td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($meses) + 1 }}" style="text-align: center;">Nenhum item de cronograma informado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    <p style="text-align: center; font-style: italic;">Período do projeto não definido ou inválido para gerar cronograma.</p>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong>7 - Recursos Necessários:</strong><br>{!! nl2br(e($projeto->recursos)) !!}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>8 - Resultados Esperados:</strong><br>{!! nl2br(e($projeto->resultados_esperados)) !!}</td>
            </tr>
            <tr>
                <td colspan="2" class="signature-section"><strong>Assinatura do(s) aluno(s) Participante(s):</strong></td>
            </tr>
            <tr>
                <td colspan="2" class="signature-section"><strong>Assinatura do(s) professor(es) Participante(s):</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Tabela de Pareceres e Aprovação --}}
    <table>
        <thead>
            <tr>
                <th colspan="2" style="text-align: center; font-size: 13px;">PARECERES E APROVAÇÃO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Data recebimento da Proposta de Projeto de Extensão pelo NAPEx:</strong><br>
                    {{ $projeto->data_entrega ? Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '__/__/____' }}
                </td>
                <td>
                    <strong>Data de Encaminhamento da Proposta do Projeto de Extensão para os devidos pareceres:</strong><br>
                    {{ $projeto->data_parecer_napex ? Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '__/__/____' }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Parecer do Núcleo de Apoio à Pesquisa e Extensão (NAPEx):</strong><br>
                    Aprovação:
                    (<span class="parecer-checkbox">{{ $projeto->aprovado_napex === 'sim' ? 'X' : ' ' }}</span>) Sim
                    (<span class="parecer-checkbox">{{ $projeto->aprovado_napex === 'nao' ? 'X' : '' }}</span>) Não
                    <br><br><strong>Exposição de motivos:</strong><br>
                    {!! nl2br(e($projeto->motivo_napex ?? '________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________')) !!}
                    <br><br>Data: {{ formatarDataPortugues($projeto->data_parecer_napex) }}.
                    <div class="signature-section" style="margin-top:10px; padding-bottom:10px;"><strong>Assinatura NAPEx:</strong></div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Parecer do coordenador de Curso:</strong><br>
                    Aprovação:
                    (<span class="parecer-checkbox">{{ $projeto->aprovado_coordenador === 'sim' ? 'X' : ' ' }}</span>) Sim
                    (<span class="parecer-checkbox">{{ $projeto->aprovado_coordenador === 'nao' ? 'X' : ' ' }}</span>) Não
                    <br><br><strong>Exposição de motivos:</strong><br>
                    {!! nl2br(e($projeto->motivo_coordenador ?? '________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________')) !!}
                    <br><br>Data: {{ formatarDataPortugues($projeto->data_parecer_coordenador) }}.
                     <div class="signature-section" style="margin-top:10px; padding-bottom:10px;"><strong>Assinatura Coordenador(a) do Curso:</strong></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>