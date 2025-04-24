@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Propostas de Projeto Extensionista <br> Curricularização da Extensão</h1>
   

    <form action="{{ route('projetos.update', $projeto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')


        <label>Título:</label>
        <input type="text" name="titulo" value="{{ $projeto->titulo }}" required>

        <label>Período:</label>
        <input type="text" name="periodo" value="{{ $projeto->periodo }}" required>

        <label>Período da Realização:</label>
        <input type="text" name="periodo_realizacao" value="{{ $projeto->periodo_realizacao }}" required>

        <label>Público Alvo:</label>
        <textarea name="publico_alvo">{{ $projeto->publico_alvo }}</textarea>

        <label>Introdução:</label>
        <textarea name="introducao">{{ $projeto->introducao }}</textarea>

        <label>Objetivo Geral:</label>
        <textarea name="objetivo_geral">{{ $projeto->objetivo_geral }}</textarea>

        <label>Justificativa:</label>
        <textarea name="justificativa">{{ $projeto->justificativa }}</textarea>

        <label>Metodologia:</label>
        <textarea name="metodologia">{{ $projeto->metodologia }}</textarea>

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

        <h3>Professores Envolvidos</h3>
        <div id="professores-wrapper">
            @foreach ($projeto->professores as $index => $prof)
                <div class="professor-group">
                    <input type="text" name="professores[{{ $index }}][nome]" value="{{ $prof->nome }}" placeholder="Nome" required>
                    <input type="email" name="professores[{{ $index }}][email]" value="{{ $prof->email }}" placeholder="Email">
                    <input type="text" name="professores[{{ $index }}][area]" value="{{ $prof->area }}" placeholder="Área">
                </div>
            @endforeach
        </div>
        <button type="button" id="add-professor">+ Adicionar Professor</button>

        <h3>Alunos Envolvidos</h3>
        <div id="alunos-wrapper">
            @foreach ($projeto->alunos as $index => $aluno)
                <div class="aluno-group">
                    <input type="text" name="alunos[{{ $index }}][nome]" value="{{ $aluno->nome }}" placeholder="Nome" required>
                    <input type="text" name="alunos[{{ $index }}][ra]" value="{{ $aluno->ra }}" placeholder="RA" required>
                    <input type="text" name="alunos[{{ $index }}][curso]" value="{{ $aluno->curso }}" placeholder="Curso" required>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-aluno">+ Adicionar Aluno</button>

        <h3>Atividades</h3>
        <div id="atividades-wrapper">
            @foreach ($projeto->atividades as $index => $atividade)
                <div class="atividade-group">
                    <textarea name="atividades[{{ $index }}][o_que_fazer]" placeholder="O que fazer?" required>{{ $atividade->o_que_fazer }}</textarea>
                    <textarea name="atividades[{{ $index }}][como_fazer]" placeholder="Como fazer?" required>{{ $atividade->como_fazer }}</textarea>
                    <input type="number" name="atividades[{{ $index }}][carga_horaria]" value="{{ $atividade->carga_horaria }}" placeholder="Carga horária" required>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-atividade">+ Adicionar Atividade</button>

        <br><br>
        <label>Arquivo (se quiser substituir):</label>
        <input type="file" name="arquivo">

        <br><br>
        <label>Status:</label>
        <select name="status" required>
            <option value="editando" {{ $projeto->status === 'editando' ? 'selected' : '' }}>Editando</option>
            <option value="entregue" {{ $projeto->status === 'entregue' ? 'selected' : '' }}>Entregue</option>
        </select>

        <br><br>
        <button type="submit">Atualizar Projeto</button>
    </form>
</div>

<script>
    let professoresWrapper = document.getElementById('professores-wrapper');
    let addProfessorBtn = document.getElementById('add-professor');
    let professorCount = {{ $projeto->professores->count() }};

    addProfessorBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('professor-group');
        div.innerHTML = `
            <input type="text" name="professores[${professorCount}][nome]" placeholder="Nome" required>
            <input type="email" name="professores[${professorCount}][email]" placeholder="Email">
            <input type="text" name="professores[${professorCount}][area]" placeholder="Área">
        `;
        professoresWrapper.appendChild(div);
        professorCount++;
    });

    let alunosWrapper = document.getElementById('alunos-wrapper');
    let addAlunoBtn = document.getElementById('add-aluno');
    let alunoCount = {{ $projeto->alunos->count() }};

    addAlunoBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('aluno-group');
        div.innerHTML = `
            <input type="text" name="alunos[${alunoCount}][nome]" placeholder="Nome" required>
            <input type="text" name="alunos[${alunoCount}][ra]" placeholder="RA" required>
            <input type="text" name="alunos[${alunoCount}][curso]" placeholder="Curso" required>
        `;
        alunosWrapper.appendChild(div);
        alunoCount++;
    });

    let atividadesWrapper = document.getElementById('atividades-wrapper');
    let addAtividadeBtn = document.getElementById('add-atividade');
    let atividadeCount = {{ $projeto->atividades->count() }};

    addAtividadeBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.classList.add('atividade-group');
        div.innerHTML = `
            <textarea name="atividades[${atividadeCount}][o_que_fazer]" placeholder="O que fazer?" required></textarea>
            <textarea name="atividades[${atividadeCount}][como_fazer]" placeholder="Como fazer?" required></textarea>
            <input type="number" name="atividades[${atividadeCount}][carga_horaria]" placeholder="Carga horária" required>
        `;
        atividadesWrapper.appendChild(div);
        atividadeCount++;
    });
</script>
@endsection
