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

        if ($role === 'coordenador') {
            return [
                'aprovado_coordenador' => 'nullable|string|in:sim,nao',
                'motivo_coordenador' => 'nullable|string',
                'data_parecer_coordenador' => 'nullable|date',
            ];
        }

        if ($role === 'napex') {
            return [
                'data_recebimento_napex' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
                'data_encaminhamento_parecer' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],


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

        // Para aluno (ou professor)
        return [
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',

            'professores' => 'nullable|array',
            'professores.*.id' => 'required_with:professores|exists:users,id',
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
            'data_recebimento_napex.regex' => 'A data de recebimento deve estar no formato DD-MM-AAAA.',
            'data_encaminhamento_parecer.regex' => 'A data de encaminhamento deve estar no formato DD-MM-AAAA.',


            'titulo.required' => 'O título do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            'professores.*.id.required_with' => 'Selecione um professor válido.',
            'professores.*.id.exists' => 'O professor selecionado não existe.',
            'professores.*.area.max' => 'A área do professor deve ter no máximo 255 caracteres.',

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
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Evita duplicidade de professores
            if ($this->has('professores')) {
                $professoresIds = collect($this->input('professores'))->pluck('id');
                if ($professoresIds->duplicates()->isNotEmpty()) {
                    $validator->errors()->add('professores', 'Você tentou adicionar o mesmo professor mais de uma vez.');
                }
            }

            // Valida ano e formato das datas
            $datas = [
                'data_recebimento_napex' => $this->input('data_recebimento_napex'),
                'data_encaminhamento_parecer' => $this->input('data_encaminhamento_parecer'),
                'data_inicio' => $this->input('data_inicio'),
                'data_fim' => $this->input('data_fim'),
                'data_parecer_coordenador' => $this->input('data_parecer_coordenador'),
            ];

            foreach ($datas as $campo => $valor) {
                if ($valor && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $matches)) {
                    $ano = (int)$matches[1];
                    $mes = (int)$matches[2];
                    $dia = (int)$matches[3];

                    if ($ano < 1000 || $ano > 9999) {
                        $validator->errors()->add($campo, 'O ano deve estar entre 1000 e 9999.');
                    }

                    if (!checkdate($mes, $dia, $ano)) {
                        $validator->errors()->add($campo, 'A data informada não é válida.');
                    }
                } elseif ($valor) {
                    $validator->errors()->add($campo, 'A data deve estar no formato AAAA-MM-DD.');
                }
            }
        });
    }

    

}
