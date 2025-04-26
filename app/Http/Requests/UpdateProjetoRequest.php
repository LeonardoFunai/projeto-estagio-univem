<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjetoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $role = auth()->user()->role;

        if ($role == 'coordenador') {
            return [
                'status' => 'required|in:editando,entregue',
                'aprovado_coordenador' => 'nullable|string|in:sim,nao',
                'motivo_coordenador' => 'nullable|string',
                'data_parecer_coordenador' => 'nullable|date',
            ];
        }

        if ($role == 'napex') {
            return [
                'status' => 'required|in:editando,entregue',
                'titulo' => 'required|string|max:255',
                'periodo' => 'required|string|max:255',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',

                'publico_alvo' => 'nullable|string',
                'introducao' => 'nullable|string',
                'objetivo_geral' => 'nullable|string',
                'justificativa' => 'nullable|string',
                'metodologia' => 'nullable|string',
                'recursos' => 'nullable|string',
                'resultados_esperados' => 'nullable|string',

                'numero_projeto' => 'nullable|string|max:255',
                'data_recebimento_napex' => 'nullable|date',
                'data_encaminhamento_parecer' => 'nullable|date',
                'aprovado_napex' => 'nullable|string|in:sim,nao',
                'motivo_napex' => 'nullable|string',

                'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',
            ];
        }

        // Se for aluno (ou professor)
        return [
            'status' => 'required|in:editando,entregue',
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',

            'professores' => 'nullable|array',
            'professores.*.nome' => 'required_with:professores|string|max:255',
            'professores.*.email' => 'nullable|email|max:255',
            'professores.*.area' => 'nullable|string|max:255',

            'alunos' => 'nullable|array',
            'alunos.*.nome' => 'required_with:alunos|string|max:255',
            'alunos.*.ra' => 'required_with:alunos|string|max:50',
            'alunos.*.curso' => 'required_with:alunos|string|max:255',

            'publico_alvo' => 'nullable|string',
            'introducao' => 'nullable|string',
            'objetivo_geral' => 'nullable|string',
            'justificativa' => 'nullable|string',
            'metodologia' => 'nullable|string',
            'recursos' => 'nullable|string',
            'resultados_esperados' => 'nullable|string',

            'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',

            'atividades' => 'nullable|array',
            'atividades.*.o_que_fazer' => 'required_with:atividades|string|max:255',
            'atividades.*.como_fazer' => 'required_with:atividades|string|max:255',
            'atividades.*.carga_horaria' => 'required_with:atividades|integer|min:1',

            'cronograma' => 'nullable|array',
            'cronograma.*.atividade' => 'required_with:cronograma|string|max:255',
            'cronograma.*.mes' => 'required_with:cronograma|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status selecionado é inválido.',

            'titulo.required' => 'O título do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            'professores.*.nome.required_with' => 'O nome do professor é obrigatório.',
            'professores.*.email.email' => 'O e-mail do professor deve ser válido.',
            'alunos.*.nome.required_with' => 'O nome do aluno é obrigatório.',
            'alunos.*.ra.required_with' => 'O RA do aluno é obrigatório.',
            'alunos.*.curso.required_with' => 'O curso do aluno é obrigatório.',

            'numero_projeto.string' => 'O número do projeto deve ser um texto.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser sim ou não.',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser sim ou não.',

            'atividades.*.o_que_fazer.required_with' => 'A descrição do que fazer na atividade é obrigatória.',
            'atividades.*.como_fazer.required_with' => 'A descrição de como fazer na atividade é obrigatória.',
            'atividades.*.carga_horaria.required_with' => 'A carga horária da atividade é obrigatória.',

            'cronograma.*.atividade.required_with' => 'O nome da atividade no cronograma é obrigatório.',
            'cronograma.*.mes.required_with' => 'O mês da atividade no cronograma é obrigatório.',

            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: jpeg, png, jpg, pdf, doc, docx.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',
        ];
    }
}
