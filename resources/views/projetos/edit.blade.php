@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Projeto de Extensão</h1>
    

    <form id="form-projeto" action="{{ route('projetos.update', $projeto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <fieldset class="fieldset-introducao">
            <legend>Introdução</legend>

            <label>Título do Projeto:</label>
            <input type="text" name="titulo" value="{{ $projeto->titulo }}" required>

            <label>Período:</label>
            <input type="text" name="periodo" value="{{ $projeto->periodo }}" required>

            <label>Professor(es) envolvidos:</label>
            <div id="professores-wrapper">
                @foreach ($projeto->professores as $index => $prof)
                    <div class="professor-group">
                        <label>Professor {{ $index + 1 }}</label>
                        <input type="text" name="professores[{{ $index }}][nome]" value="{{ $prof->nome }}" required>
                        <input type="email" name="professores[{{ $index }}][email]" value="{{ $prof->email }}" placeholder="Email (opcional)">
                        <input type="text" name="professores[{{ $index }}][area]" value="{{ $prof->area }}" placeholder="Área (opcional)">
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-professor">+ Adicionar Professor</button>

            <label>Alunos envolvidos / R.A / Curso:</label>
            <div id="alunos-wrapper">
                @foreach ($projeto->alunos as $index => $aluno)
                    <div class="aluno-group">
                        <label>Aluno {{ $index + 1 }}</label>
                        <input type="text" name="alunos[{{ $index }}][nome]" value="{{ $aluno->nome }}" required>
                        <input type="text" name="alunos[{{ $index }}][ra]" value="{{ $aluno->ra }}" required>
                        <input type="text" name="alunos[{{ $index }}][curso]" value="{{ $aluno->curso }}" required>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-aluno">+ Adicionar Aluno</button>

            <label>Público Alvo:</label>
            <textarea name="publico_alvo">{{ $projeto->publico_alvo }}</textarea>
        </fieldset>

        <fieldset class="fieldset-detalhes">
            <legend>Detalhes do Projeto</legend>

            <label>Data de Início:</label>
            <input type="date" name="data_inicio" id="data_inicio" value="{{ \Carbon\Carbon::parse($projeto->data_inicio)->format('Y-m-d') }}" required>

            <label>Data de Término:</label>
            <input type="date" name="data_fim" id="data_fim" value="{{ \Carbon\Carbon::parse($projeto->data_fim)->format('Y-m-d') }}" required>

            <label>Introdução:</label>
            <textarea name="introducao">{{ $projeto->introducao }}</textarea>

            <label>Objetivo Geral:</label>
            <textarea name="objetivo_geral">{{ $projeto->objetivo_geral }}</textarea>

            <label>Justificativa:</label>
            <textarea name="justificativa">{{ $projeto->justificativa }}</textarea>

            <label>Metodologia:</label>
            <textarea name="metodologia">{{ $projeto->metodologia }}</textarea>

            <label>Atividades a serem desenvolvidas:</label>
            <div id="atividades-wrapper">
                @foreach ($projeto->atividades as $index => $atividade)
                    <div class="atividade-group">
                        <label>Atividade {{ $index + 1 }}</label>
                        <textarea name="atividades[{{ $index }}][o_que_fazer]" required>{{ $atividade->o_que_fazer }}</textarea>
                        <textarea name="atividades[{{ $index }}][como_fazer]" required>{{ $atividade->como_fazer }}</textarea>
                        <input type="number" name="atividades[{{ $index }}][carga_horaria]" value="{{ $atividade->carga_horaria }}" required>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-atividade">+ Adicionar Atividade</button>

            <label>Execução do Projeto:</label>
            <textarea name="execucao_projeto">{{ $projeto->execucao_projeto }}</textarea>

            <label>Documentação da Execução:</label>
            <textarea name="documentacao_execucao">{{ $projeto->documentacao_execucao }}</textarea>

            <label>Relatório Final:</label>
            <textarea name="relatorio_final">{{ $projeto->relatorio_final }}</textarea>

            <label>Cronograma:</label>
            <textarea name="cronograma">{{ $projeto->cronograma }}</textarea>

            <label>Recursos:</label>
            <textarea name="recursos">{{ $projeto->recursos }}</textarea>

            <label>Resultados Esperados:</label>
            <textarea name="resultados_esperados">{{ $projeto->resultados_esperados }}</textarea>

            <label>Arquivo (opcional):</label>
            <input type="file" name="arquivo">

            <label>Status:</label>
            <select name="status" required>
                <option value="editando" {{ $projeto->status === 'editando' ? 'selected' : '' }}>Editando</option>
                <option value="entregue" {{ $projeto->status === 'entregue' ? 'selected' : '' }}>Entregue</option>
            </select>

            <br><br>
            <button type="submit">Atualizar Projeto</button>
        </fieldset>
    </form>
</div>

<script>
    let professoresWrapper = document.getElementById('professores-wrapper');
    let addProfessorBtn = document.getElementById('add-professor');
    let professorCount = {{ $projeto->professores->count() }};

    addProfessorBtn.addEventListener('click', function () {
        if (professorCount < 9) {
            const div = document.createElement('div');
            div.classList.add('professor-group');
            div.innerHTML = `
                <label>Professor ${professorCount + 1}</label>
                <input type="text" name="professores[${professorCount}][nome]" placeholder="Nome do professor" required>
                <input type="email" name="professores[${professorCount}][email]" placeholder="Email (opcional)">
                <input type="text" name="professores[${professorCount}][area]" placeholder="Área (opcional)">
            `;
            professoresWrapper.appendChild(div);
            professorCount++;
        } else {
            alert('Você só pode adicionar até 9 professores.');
        }
    });

    let alunosWrapper = document.getElementById('alunos-wrapper');
    let addAlunoBtn = document.getElementById('add-aluno');
    let alunoCount = {{ $projeto->alunos->count() }};

    addAlunoBtn.addEventListener('click', function () {
        if (alunoCount < 9) {
            const div = document.createElement('div');
            div.classList.add('aluno-group');
            div.innerHTML = `
                <label>Aluno ${alunoCount + 1}</label>
                <input type="text" name="alunos[${alunoCount}][nome]" placeholder="Nome do aluno" required>
                <input type="text" name="alunos[${alunoCount}][ra]" placeholder="RA" required>
                <input type="text" name="alunos[${alunoCount}][curso]" placeholder="Curso" required>
            `;
            alunosWrapper.appendChild(div);
            alunoCount++;
        } else {
            alert('Você só pode adicionar até 9 alunos.');
        }
    });

    let atividadesWrapper = document.getElementById('atividades-wrapper');
    let addAtividadeBtn = document.getElementById('add-atividade');
    let atividadeCount = {{ $projeto->atividades->count() }};

    addAtividadeBtn.addEventListener('click', function () {
        const div = document.createElement('div');
        div.classList.add('atividade-group');
        div.innerHTML = `
            <label>Atividade ${atividadeCount + 1}</label>
            <textarea name="atividades[${atividadeCount}][o_que_fazer]" placeholder="O que fazer?" required></textarea>
            <textarea name="atividades[${atividadeCount}][como_fazer]" placeholder="Como fazer?" required></textarea>
            <input type="number" name="atividades[${atividadeCount}][carga_horaria]" placeholder="Carga horária" required>
        `;
        atividadesWrapper.appendChild(div);
        atividadeCount++;
    });

    document.getElementById('form-projeto').addEventListener('submit', function (e) {
        const inicio = document.getElementById('data_inicio').value;
        const fim = document.getElementById('data_fim').value;

        if (!inicio || !fim || new Date(inicio) > new Date(fim)) {
            e.preventDefault();
            alert('A data de início deve ser anterior ou igual à data de fim.');
        }
    });
</script>
@endsection
