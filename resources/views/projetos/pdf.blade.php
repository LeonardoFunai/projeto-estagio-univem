@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    // Garante valores padrão para as datas do projeto se estiverem nulas para evitar erros com Carbon.
    $dataInicioInputForView = $projeto->data_inicio ?? null; // Usado para exibição do período do projeto na Tabela de Identificação
    $dataFimInputForView = $projeto->data_fim ?? null;     // Usado para exibição do período do projeto na Tabela de Identificação

    // Variáveis para cálculo dos meses das colunas do cronograma
    $_inicioCarbonParaMeses = null;
    $_fimCarbonParaMeses = null;
    $_datasValidasParaGerarMeses = false;

    // Tenta criar datas Carbon para gerar as colunas de meses do cronograma
    if ($projeto->data_inicio && $projeto->data_fim) {
        try {
            $tempInicio = Carbon::createFromFormat('Y-m-d', $projeto->data_inicio)->startOfDay();
            $tempFim = Carbon::createFromFormat('Y-m-d', $projeto->data_fim)->endOfDay();
            if ($tempInicio->isValid() && $tempFim->isValid() && $tempInicio->lte($tempFim)) {
                $_inicioCarbonParaMeses = $tempInicio;
                $_fimCarbonParaMeses = $tempFim;
                $_datasValidasParaGerarMeses = true;
            }
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            // Log::error("Erro ao parsear datas para gerar meses do cronograma no PDF do projeto {$projeto->id}: " . $e->getMessage());
        }
    }

    $mesesColunas = []; // Meses que serão as colunas da tabela de cronograma (em inglês, de format('F'))
    if ($_datasValidasParaGerarMeses) {
        $currentMonth = $_inicioCarbonParaMeses->copy();
        while ($currentMonth->lte($_fimCarbonParaMeses)) {
            $mesesColunas[] = $currentMonth->format('F'); // 'F' para nome completo do mês em inglês
            $currentMonth->addMonthWithOverflow();
        }
        $mesesColunas = array_unique($mesesColunas);
    }

    // Array para tradução dos nomes dos meses (Inglês -> Português)
    $traducaoMesesEnParaPt = [
        'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
        'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
        'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
        'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro',
    ];

    // Mapeamento de meses em português para números (para comparação de intervalo no cronograma)
    $mesesPtParaNumero = [
        'Janeiro' => 1, 'Fevereiro' => 2, 'Março' => 3, 'Abril' => 4,
        'Maio' => 5, 'Junho' => 6, 'Julho' => 7, 'Agosto' => 8,
        'Setembro' => 9, 'Outubro' => 10, 'Novembro' => 11, 'Dezembro' => 12,
    ];

    // Mapeamento de meses em inglês (gerados por format('F')) para números
    $mesesEnParaNumero = [
        'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
        'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
        'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
    ];

    // Função helper para formatar datas no padrão "dd de MMMM de YYYY" em português.
    if (!function_exists('formatarDataPortuguesPdfParaArquivo')) { // Nome único para esta view
        function formatarDataPortuguesPdfParaArquivo($data, array $mapaTraducaoMeses) {
            if ($data) {
                try {
                    $carbonData = \Carbon\Carbon::parse($data);
                    $mesIngles = $carbonData->format('F');
                    $mesPortugues = $mapaTraducaoMeses[$mesIngles] ?? $mesIngles;
                    return $carbonData->format('d') . ' de ' . $mesPortugues . ' de ' . $carbonData->format('Y');
                } catch (Exception $e) {
                    if ($data instanceof \Carbon\Carbon) { return $data->format('d/m/Y'); }
                    $timestamp = strtotime($data);
                    if ($timestamp !== false) { return date('d/m/Y', $timestamp); }
                    return 'Data inválida';
                }
            }
            return '____ de _______________ de _______';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta de Atividade Extensionista - {{ $projeto->numero_projeto ?? 'N/A' }}</title>
    <style>
        @page {
            margin-top: 160px; /* Espaço para header */
            margin-bottom: 80px; /* Espaço para footer */
            margin-left: 20px;
            margin-right: 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        header {
            position: fixed;
            top: -140px; /* Deve ser negativo e relacionado ao margin-top de @page */
            left: 0px;
            right: 0px;
            height: 120px; /* Altura efetiva do conteúdo do header */
            background-color: #fff;
        }
        footer {
            position: fixed;
            bottom: -60px; /* Deve ser negativo e relacionado ao margin-bottom de @page */
            left: 0px;
            right: 0px;
            height: 60px;
            font-size: 9px;
            color: #002c74;
            border-top: 1px solid #000;
            padding: 5px 20px;
            background-color: #fff;
            text-align: center;
        }
        footer p {
            margin: 1px 0;
        }
        .content-wrapper {
            /* O conteúdo começa após a margem definida em @page, não precisa de padding-top aqui */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
            line-height: 1.3;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word; /* Quebra palavras longas para evitar overflow */
            /* hyphens: auto; pode não ser bem suportado em todos os renderizadores de PDF */
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .no-border-table,
        .no-border-table td,
        .no-border-table th {
            border: none !important;
            padding: 0;
        }
        .header-logo-table {
            width: 100%;
            margin-bottom: 5px;
        }
        .header-logo-table td {
            vertical-align: middle;
        }
        .header-logo-table img {
            vertical-align: middle;
            height: 50px;
        }
        .header-title-div {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            width: 100%;
            line-height: 1.3;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding-top: 5px;
            padding-bottom: 5px;
            margin-top: 5px;
        }
        .header-title-div span {
            display: block;
        }
        .header-title-div .resolution-text {
            font-size: 10px;
            font-weight: normal;
        }
        .nested-table {
            margin-top: 5px;
            margin-bottom: 0;
            font-size: 10px;
        }
        .nested-table th, .nested-table td {
            padding: 3px;
            border: 1px solid #555;
        }
        .nested-table th {
            background-color: #f5f5f5;
        }
        .signature-section {
            padding-top: 10px;
            padding-bottom: 30px;
        }
        .signature-section strong {
            display: block;
            margin-bottom: 30px;
        }
        .parecer-checkbox {
            font-family: 'DejaVu Sans', sans-serif; /* Para o 'X' ou '✔' */
            display: inline-block;
            width: 13px; /* Ajustado */
            height: 13px; /* Ajustado */
            line-height: 13px; /* Ajustado */
            text-align: center;
            border: 1px solid #333;
            margin-right: 2px;
            vertical-align: middle; /* Alinha melhor com o texto */
        }
        .section-title th {
            text-align: center !important;
            font-size: 13px !important;
            background-color: #e0e0e0 !important;
        }
        .text-content {
             white-space: pre-wrap;
        }
    </style>
</head>
<body>

    <header>
        <table class="no-border-table header-logo-table">
            <tr>
                <td style="width: 80px; text-align: left;">
                    <img src="{{ public_path('img/site/logo-pdf.png') }}" alt="Logo">
                </td>
                <td style="text-align: left; vertical-align: middle; padding-left: 10px;">
                    <strong style="font-size: 9px; display:block; line-height: 1.2;">
                        MANTIDO PELA FUNDAÇÃO DE ENSINO “EURÍPIDES SOARES DA ROCHA”
                    </strong>
                    <span style="font-size: 8px; display:block; line-height: 1.2;">Centro Universitário Eurípedes de Marília</span>
                </td>
            </tr>
        </table>
        <div class="header-title-div">
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
                    <th colspan="3" class="section-title">IDENTIFICAÇÃO</th>
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
                    <td colspan="3"><strong>Professor(es) envolvidos:</strong>
                        @if($projeto->professores && $projeto->professores->count() > 0)
                            {{ $projeto->professores->map(function($prof) { return $prof->user->name ?? ($prof->nome_completo ?? 'Nome Indisponível'); })->implode(', ') }}
                        @else
                            Nenhum professor informado.
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; background-color: #f0f0f0; font-weight:bold;"><strong>Alunos envolvidos:</strong></td>
                </tr>
                <tr>
                    <th style="width: 40%;">Nome Completo</th>
                    <th style="width: 20%;">R.A</th>
                    <th style="width: 40%;">Curso</th>
                </tr>
                @forelse ($projeto->alunos as $aluno)
                <tr>
                    <td>{{ $aluno->nome_completo ?? $aluno->nome }}</td>
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
                        @php
                            $_dataInicioFormatadaPdf = '__/__/____';
                            $_dataFimFormatadaPdf = '__/__/____';
                            if ($dataInicioInputForView) { // Usando a variável definida no @php do topo
                                try { $_dataInicioFormatadaPdf = \Carbon\Carbon::parse($dataInicioInputForView)->format('d/m/Y'); } catch (\Exception $e) {}
                            }
                            if ($dataFimInputForView) { // Usando a variável definida no @php do topo
                                try { $_dataFimFormatadaPdf = \Carbon\Carbon::parse($dataFimInputForView)->format('d/m/Y'); } catch (\Exception $e) {}
                            }
                        @endphp
                        {{ $_dataInicioFormatadaPdf }} a {{ $_dataFimFormatadaPdf }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Tabela de Descrição do Projeto --}}
        <table>
            <thead>
                <tr>
                    <th colspan="2" class="section-title">DESCRIÇÃO DO PROJETO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" class="text-content"><strong>1 - Introdução:</strong><br>{!! nl2br(e($projeto->introducao)) !!}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-content"><strong>2 - Objetivo Geral:</strong><br>{!! nl2br(e($projeto->objetivo_geral)) !!}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-content"><strong>3 - Justificativa:</strong><br>{!! nl2br(e($projeto->justificativa)) !!}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-content"><strong>4 - Metodologia:</strong><br>{!! nl2br(e($projeto->metodologia)) !!}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>5 - Atividades a serem desenvolvidas:</strong>
                        @if($projeto->atividades && $projeto->atividades->count() > 0)
                        <table class="nested-table">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">O que fazer?</th>
                                    <th style="width: 45%;">Como fazer?</th>
                                    <th style="width: 10%; text-align:center;">Carga horária</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projeto->atividades as $a)
                                <tr>
                                    <td class="text-content">{{ $a->o_que_fazer }}</td>
                                    <td class="text-content">{{ $a->como_fazer }}</td>
                                    <td style="text-align:center;">{{ $a->carga_horaria }}h</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p style="text-align: left; font-style: italic; padding-left: 10px;">Nenhuma atividade informada.</p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>6 - Cronograma:</strong>
                        @if (!empty($mesesColunas) && $projeto->cronogramas && $projeto->cronogramas->count() > 0)
                        <table class="nested-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Atividade do Cronograma</th>
                                    @foreach ($mesesColunas as $mEng)
                                        <th style="text-align:center;">
                                            {{ $traducaoMesesEnParaPt[$mEng] ?? $mEng }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projeto->cronogramas as $itemDoCronograma)
                                <tr>
                                    <td>{{ $itemDoCronograma->atividade }}</td>
                                    @php
                                        $numMesInicioAtividade = $mesesPtParaNumero[trim((string)$itemDoCronograma->mes_inicio)] ?? 0;
                                        $numMesFimAtividade = $mesesPtParaNumero[trim((string)$itemDoCronograma->mes_fim)] ?? 0;
                                    @endphp
                                    @foreach ($mesesColunas as $mEng)
                                        @php
                                            $numMesColuna = $mesesEnParaNumero[trim((string)$mEng)] ?? -1;
                                            $marcarCelula = '';
                                            if ($numMesInicioAtividade > 0 && $numMesFimAtividade > 0 && $numMesColuna >= 0) {
                                                if ($numMesFimAtividade >= $numMesInicioAtividade) { // Período normal
                                                    if ($numMesColuna >= $numMesInicioAtividade && $numMesColuna <= $numMesFimAtividade) {
                                                        $marcarCelula = '✔';
                                                    }
                                                } else { // Caso onde mes_fim < mes_inicio (dados podem estar incorretos)
                                                    if ($numMesColuna == $numMesInicioAtividade) { // Marcar apenas o início neste caso
                                                        $marcarCelula = '✔';
                                                    }
                                                }
                                            }
                                        @endphp
                                        <td style="text-align:center;">{{ $marcarCelula }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @elseif (!$projeto->cronogramas || $projeto->cronogramas->count() == 0)
                            <p style="text-align: left; font-style: italic; padding-left: 10px;">Nenhum item de cronograma informado.</p>
                        @else
                            <p style="text-align: left; font-style: italic; padding-left: 10px;">Período do projeto (datas de início/fim) não definido ou inválido para gerar a tabela de cronograma.</p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="text-content"><strong>7 - Recursos Necessários:</strong><br>{!! nl2br(e($projeto->recursos)) !!}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-content"><strong>8 - Resultados Esperados:</strong><br>{!! nl2br(e($projeto->resultados_esperados)) !!}</td>
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
                    <th colspan="2" class="section-title">PARECERES E APROVAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Data recebimento da Proposta de Projeto de Extensão pelo NAPEx:</strong><br>
                        {{ $projeto->data_entrega ? \Carbon\Carbon::parse($projeto->data_entrega)->format('d/m/Y') : '__/__/____' }}
                    </td>
                    <td>
                        <strong>Data de Encaminhamento da Proposta do Projeto de Extensão para os devidos pareceres:</strong><br>
                        {{ $projeto->data_parecer_napex ? \Carbon\Carbon::parse($projeto->data_parecer_napex)->format('d/m/Y') : '__/__/____' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Parecer do Núcleo de Apoio à Pesquisa e Extensão (NAPEx):</strong><br>
                        Aprovação:
                        (<span class="parecer-checkbox">{{ $projeto->aprovado_napex === 'sim' ? 'X' : '' }}</span>) Sim
                        (<span class="parecer-checkbox">{{ $projeto->aprovado_napex === 'nao' ? 'X' : '' }}</span>) Não
                        <br><br><strong>Exposição de motivos:</strong><br>
                        <div class="text-content">{!! nl2br(e($projeto->motivo_napex ?? '________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________')) !!}</div>
                        <br>Data: {{ formatarDataPortuguesPdfParaArquivo($projeto->data_parecer_napex, $traducaoMesesEnParaPt) }}.
                        <div class="signature-section" style="margin-top:10px; padding-bottom:10px;"><strong>Assinatura NAPEx:</strong></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Parecer do coordenador de Curso:</strong><br>
                        Aprovação:
                        (<span class="parecer-checkbox">{{ $projeto->aprovado_coordenador === 'sim' ? 'X' : '' }}</span>) Sim
                        (<span class="parecer-checkbox">{{ $projeto->aprovado_coordenador === 'nao' ? 'X' : '' }}</span>) Não
                        <br><br><strong>Exposição de motivos:</strong><br>
                        <div class="text-content">{!! nl2br(e($projeto->motivo_coordenador ?? '________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________')) !!}</div>
                        <br>Data: {{ formatarDataPortuguesPdfParaArquivo($projeto->data_parecer_coordenador, $traducaoMesesEnParaPt) }}.
                        <div class="signature-section" style="margin-top:10px; padding-bottom:10px;"><strong>Assinatura Coordenador(a) do Curso:</strong></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>