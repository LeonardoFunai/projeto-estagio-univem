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
        return [
            'status' => 'required|in:editando,entregue',
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',

            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',

            'professores' => 'required|array|min:1|max:9',
            'professores.*.nome' => 'required|string|max:255',
            'professores.*.email' => 'nullable|email|max:255',
            'professores.*.area' => 'nullable|string|max:255',

            'publico_alvo' => 'nullable|string',
            'introducao' => 'nullable|string',
            'objetivo_geral' => 'nullable|string',
            'justificativa' => 'nullable|string',
            'metodologia' => 'nullable|string',
            'o_que_fazer' => 'nullable|string',
            'como_fazer' => 'nullable|string',
            'carga_horaria' => 'nullable|integer|min:0',
            'execucao_projeto' => 'nullable|string',
            'documentacao_execucao' => 'nullable|string',
            'relatorio_final' => 'nullable|string',
            'cronograma' => 'nullable|string',
            'recursos' => 'nullable|string',
            'resultados_esperados' => 'nullable|string',

            'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',

            'alunos' => 'required|array|min:1|max:9',
            'alunos.*.nome' => 'required|string|max:255',
            'alunos.*.ra' => 'required|string|max:50',
            'alunos.*.curso' => 'required|string|max:255',
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

            'alunos.required' => 'Adicione pelo menos um aluno ao projeto.',
            'alunos.*.nome.required' => 'O nome do aluno é obrigatório.',
            'alunos.*.ra.required' => 'O RA do aluno é obrigatório.',
            'alunos.*.curso.required' => 'O curso do aluno é obrigatório.',

            'professores.required' => 'Adicione pelo menos um professor ao projeto.',
            'professores.*.nome.required' => 'O nome do professor é obrigatório.',
            'professores.*.email.email' => 'O e-mail do professor deve ser válido.',
        ];
    }
}
