@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/show.css') }}">

<h1>Detalhes da Proposta de Projeto Extensionista Curricularização da Extensão</h1>

<table>
    <tr><th>Título</th><td>{{ $projeto->titulo }}</td></tr>
    <tr><th>Período</th><td>{{ $projeto->periodo }}</td></tr>
    <tr><th>Data de Início</th><td>{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('d/m/Y') }}</td></tr>
    <tr><th>Data de Término</th><td>{{ \Carbon\Carbon::parse($projeto->data_fim)->format('d/m/Y') }}</td></tr>

    <tr>
        <th>Professores Envolvidos</th>
        <td>
            @if ($projeto->professores && $projeto->professores->count())
                <ul>
                    @foreach ($projeto->professores as $prof)
                        <li>
                            <strong>{{ $prof->nome }}</strong>
                            @if($prof->email) – Email: {{ $prof->email }} @endif
                            @if($prof->area) – Área: {{ $prof->area }} @endif
                        </li>
                    @endforeach
                </ul>
            @else
                Nenhum professor registrado.
            @endif
        </td>
    </tr>

    <tr>
        <th>Alunos Envolvidos</th>
        <td>
            @if ($projeto->alunos && $projeto->alunos->count())
                <ul>
                    @foreach ($projeto->alunos as $aluno)
                        <li><strong>{{ $aluno->nome }}</strong> — RA: {{ $aluno->ra }} — Curso: {{ $aluno->curso }}</li>
                    @endforeach
                </ul>
            @else
                Nenhum aluno registrado.
            @endif
        </td>
    </tr>

    <tr><th>Público Alvo</th><td>{{ $projeto->publico_alvo }}</td></tr>
    <tr><th>Introdução</th><td>{{ $projeto->introducao }}</td></tr>
    <tr><th>Objetivo Geral</th><td>{{ $projeto->objetivo_geral }}</td></tr>
    <tr><th>Justificativa</th><td>{{ $projeto->justificativa }}</td></tr>
    <tr><th>Metodologia</th><td>{{ $projeto->metodologia }}</td></tr>
    <tr><th>Execução do Projeto</th><td>{{ $projeto->execucao_projeto }}</td></tr>
    <tr><th>Documentação da Execução</th><td>{{ $projeto->documentacao_execucao }}</td></tr>
    <tr><th>Relatório Final</th><td>{{ $projeto->relatorio_final }}</td></tr>
    <tr><th>Cronograma</th><td>{{ $projeto->cronograma }}</td></tr>
    <tr><th>Recursos</th><td>{{ $projeto->recursos }}</td></tr>
    <tr><th>Resultados Esperados</th><td>{{ $projeto->resultados_esperados }}</td></tr>

    <tr>
        <th>Atividades</th>
        <td>
            @if ($projeto->atividades && $projeto->atividades->count())
                <ul>
                    @foreach ($projeto->atividades as $atividade)
                        <li>
                            <p><strong>O que fazer:</strong> {{ $atividade->o_que_fazer }}</p>
                            <p><strong>Como fazer:</strong> {{ $atividade->como_fazer }}</p>
                            <p><strong>Carga Horária:</strong> {{ $atividade->carga_horaria }} horas</p>
                        </li>
                    @endforeach
                </ul>
            @else
                Nenhuma atividade registrada.
            @endif
        </td>
    </tr>

    <tr>
        <th>Arquivo</th>
        <td>
            @if ($projeto->arquivo)
                <p>
                    <a href="{{ route('projetos.download', $projeto->id) }}" class="btn btn-primary" target="_blank">
                        📄 Ver/Download do Arquivo
                    </a>
                </p>
            @endif
        </td>
    </tr>

    <tr><th>Data de Cadastro</th><td>{{ $projeto->created_at->format('d/m/Y H:i') }}</td></tr>
    <tr><th>Última Atualização</th><td>{{ $projeto->updated_at->format('d/m/Y H:i') }}</td></tr>
    <tr><th>Status</th><td>{{ ucfirst($projeto->status) }}</td></tr>
</table>



@endsection
