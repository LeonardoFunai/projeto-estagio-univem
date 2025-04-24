@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cadastro de Projeto de Extensão</h1>
    <form id="form-projeto" action="{{ route('projetos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <h2>1. Identificação</h2>
        <label>Título do Projeto:</label>
        <input type="text" name="titulo" required>

        <label>Período:</label>
        <input type="text" name="periodo" required>

        <label>Professor(es) envolvidos:</label>
        <div id="professores-wrapper">
            <div class="professor-group">
                <input type="text" name="professores[0][nome]" placeholder="Nome do professor" required>
                <input type="email" name="professores[0][email]" placeholder="Email (opcional)">
                <input type="text" name="professores[0][area]" placeholder="Área (opcional)">
            </div>
        </div>
        <button type="button" id="add-professor">+ Adicionar Professor</button>

        <label>Alunos envolvidos / R.A / Curso:</label>
        <div id="alunos-wrapper">
            <div class="aluno-group">
                <input type="text" name="alunos[0][nome]" placeholder="Nome do aluno" required>
                <input type="text" name="alunos[0][ra]" placeholder="RA" required>
                <input type="text" name="alunos[0][curso]" placeholder="Curso" required>
            </div>
        </div>
        <button type="button" id="add-aluno">+ Adicionar Aluno</button>

        <h2>2. Público Alvo</h2>
        <textarea name="publico_alvo"></textarea>

        <h2>3. Período da Realização</h2>
        <label>Data de Início:</label>
        <input type="date" name="data_inicio" id="data_inicio" required>
        <label>Data de Término:</label>
        <input type="date" name="data_fim" id="data_fim" required>

        <h2>4. Introdução</h2>
        <textarea name="introducao"></textarea>

        <h2>5. Objetivos do Projeto</h2>
        <textarea name="objetivo_geral"></textarea>

        <h2>6. Justificativa</h2>
        <textarea name="justificativa"></textarea>

        <h2>7. Metodologia</h2>
        <textarea name="metodologia"></textarea>

        <h2>8. Atividades a serem desenvolvidas</h2>
        <div id="atividades-wrapper">
            <div class="atividade-group">
                <textarea name="atividades[0][o_que_fazer]" placeholder="O que fazer?" required></textarea>
                <textarea name="atividades[0][como_fazer]" placeholder="Como fazer?" required></textarea>
                <input type="number" name="atividades[0][carga_horaria]" placeholder="Carga horária" required>
            </div>
        </div>
        <button type="button" id="add-atividade">+ Adicionar Atividade</button>

        <h2>9. Execução do Projeto</h2>
        <textarea name="execucao_projeto"></textarea>

        <label>Documentação da execução:</label>
        <textarea name="documentacao_execucao"></textarea>

        <label>Relatório Final:</label>
        <textarea name="relatorio_final"></textarea>

        <h2>10. Cronograma</h2>
        <textarea name="cronograma"></textarea>

        <h2>11. Recursos Necessários</h2>
        <textarea name="recursos"></textarea>

        <h2>12. Resultados Esperados</h2>
        <textarea name="resultados_esperados"></textarea>

        <h2>13. Arquivo (opcional)</h2>
        <input type="file" name="arquivo">

        <label>Status:</label>
        <select name="status" required>
            <option value="editando" selected>Editando</option>
            <option value="entregue">Entregue</option>
        </select>

        <br><br>
        <button type="submit">Salvar Projeto</button>
    </form>
</div>

<script>
    let professoresWrapper = document.getElementById('professores-wrapper');
    let addProfessorBtn = document.getElementById('add-professor');
    let professorCount = 1;

    addProfessorBtn.addEventListener('click', function () {
        if (professorCount < 9) {
            const div = document.createElement('div');
            div.classList.add('professor-group');
            div.innerHTML = `
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
    let alunoCount = 1;

    addAlunoBtn.addEventListener('click', function () {
        if (alunoCount < 9) {
            const div = document.createElement('div');
            div.classList.add('aluno-group');
            div.innerHTML = `
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
    let atividadeCount = 1;

    addAtividadeBtn.addEventListener('click', function () {
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
