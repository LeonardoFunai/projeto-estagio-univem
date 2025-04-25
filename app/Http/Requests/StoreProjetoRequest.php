<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjetoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Dados do Projeto
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',

            // Professores
            'professores' => 'required|array|min:1|max:9',
            'professores.*.nome' => 'required|string|max:255',
            'professores.*.email' => 'nullable|email|max:255',
            'professores.*.area' => 'nullable|string|max:255',

            // Alunos
            'alunos' => 'required|array|min:1|max:9',
            'alunos.*.nome' => 'required|string|max:255',
            'alunos.*.ra' => 'required|string|max:50',
            'alunos.*.curso' => 'required|string|max:255',

            // Publico Alvo e Descrição do Projeto
            'publico_alvo' => 'nullable|string',
            'introducao' => 'nullable|string',
            'objetivo_geral' => 'nullable|string',
            'justificativa' => 'nullable|string',
            'metodologia' => 'nullable|string',
            'recursos' => 'nullable|string',
            'resultados_esperados' => 'nullable|string',

            // Pareceres NAPEx e Coordenador
            'numero_projeto' => 'nullable|string|max:255',
            'data_recebimento_napex' => 'nullable|date',
            'data_encaminhamento_parecer' => 'nullable|date',
            'aprovado_napex' => 'nullable|string|in:sim,nao',
            'motivo_napex' => 'nullable|string',
            'aprovado_coordenador' => 'nullable|string|in:sim,nao',
            'motivo_coordenador' => 'nullable|string',
            'data_parecer_coordenador' => 'nullable|date',

            // Arquivo
            'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',

            // Atividades
            'atividades' => 'nullable|array|min:1|max:10',  // Permitindo até 10 atividades
            'atividades.*.o_que_fazer' => 'required|string|max:255',
            'atividades.*.como_fazer' => 'required|string|max:255',
            'atividades.*.carga_horaria' => 'required|integer|min:1',

            // Cronograma (Nova Tabela para o Cronograma)
            'cronograma' => 'nullable|array|min:1|max:10',  // Permitindo até 10 cronogramas
            'cronograma.*.atividade' => 'required|string|max:255',
            'cronograma.*.mes' => 'required|string|max:20',  // Validação do mês (Ex: Janeiro, Fevereiro, etc.)
        ];
    }

    public function messages()
    {
        return [
            // Mensagens de Erro para o Projeto
            'titulo.required' => 'O título do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            // Mensagens para Alunos
            'alunos.required' => 'Adicione pelo menos um aluno ao projeto.',
            'alunos.*.nome.required' => 'O nome do aluno é obrigatório.',
            'alunos.*.ra.required' => 'O RA do aluno é obrigatório.',
            'alunos.*.curso.required' => 'O curso do aluno é obrigatório.',

            // Mensagens para Professores
            'professores.required' => 'Adicione pelo menos um professor ao projeto.',
            'professores.*.nome.required' => 'O nome do professor é obrigatório.',
            'professores.*.email.email' => 'O e-mail do professor deve ser válido.',

            // Mensagens para o Parecer NAPEx e Coordenador
            'numero_projeto.string' => 'O número do projeto deve ser um texto.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser sim ou não.',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser sim ou não.',
            'data_recebimento_napex.date' => 'A data de recebimento pelo NAPEx deve ser uma data válida.',
            'data_encaminhamento_parecer.date' => 'A data de encaminhamento para os pareceres deve ser uma data válida.',
            'data_parecer_coordenador.date' => 'A data do parecer do coordenador deve ser uma data válida.',

            // Mensagens de Arquivo
            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: jpeg, png, jpg, pdf, doc, docx.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',

            // Mensagens para Atividades
            'atividades.*.o_que_fazer.required' => 'A descrição do que fazer na atividade é obrigatória.',
            'atividades.*.como_fazer.required' => 'A descrição de como fazer na atividade é obrigatória.',
            'atividades.*.carga_horaria.required' => 'A carga horária da atividade é obrigatória.',

            // Mensagens para Cronograma
            'cronograma.*.atividade.required' => 'O nome da atividade no cronograma é obrigatório.',
            'cronograma.*.mes.required' => 'O mês da atividade no cronograma é obrigatório.',
        ];
    }
}
