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
            'data_recebimento_napex' => ['nullable', 'date', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'data_encaminhamento_parecer' => ['nullable', 'date', 'regex:/^\d{4}-\d{2}-\d{2}$/'],

            // Dados do Projeto
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:50',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',

            // Professores
            'professores' => 'required|array|min:1|max:9',
            'professores.*.id' => 'required|integer|exists:users,id',
            'professores.*.area' => 'nullable|string|max:100',

            // Alunos
            'alunos' => 'required|array|min:1|max:9',
            'alunos.*.nome' => 'required|string|max:100',
            'alunos.*.ra' => 'required|string|max:50',
            'alunos.*.curso' => 'required|string|max:100',

            // Descrição do Projeto com limites definidos
            'publico_alvo' => 'nullable|string|max:100',
            'introducao' => 'nullable|string|max:1000',
            'objetivo_geral' => 'nullable|string|max:1000',
            'justificativa' => 'nullable|string|max:1000',
            'metodologia' => 'nullable|string|max:500',
            'recursos' => 'nullable|string',
            'resultados_esperados' => 'nullable|string|max:1000',

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

            // Atividades (texto e carga horária)
            'atividades' => 'nullable|array|min:1|max:10',
            'atividades.*.o_que_fazer' => 'required|string|max:1000',
            'atividades.*.como_fazer' => 'required|string|max:1000',
            'atividades.*.carga_horaria' => 'required|integer|min:1|max:99999',

            // Cronograma
            'cronograma' => 'nullable|array|min:1|max:10',
            'cronograma.*.atividade' => 'required|string|max:100',
            'cronograma.*.mes' => 'required|string|in:Fevereiro,Março,Abril,Maio,Junho,Julho,Agosto,Setembro,Outubro,Novembro',
        ];
    }

    

    public function messages()
    {
        return [
            'data_recebimento_napex.regex' => 'A data de recebimento do NAPEx deve estar no formato AAAA-MM-DD com ano de 4 dígitos.',
            'data_encaminhamento_parecer.regex' => 'A data de encaminhamento deve estar no formato AAAA-MM-DD com ano de 4 dígitos.',

            // Projeto
            'titulo.required' => 'O título do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
    
            // Alunos
            'alunos.required' => 'Adicione pelo menos um aluno ao projeto.',
            'alunos.*.nome.required' => 'O nome do aluno é obrigatório.',
            'alunos.*.ra.required' => 'O RA do aluno é obrigatório.',
            'alunos.*.curso.required' => 'O curso do aluno é obrigatório.',
    
            // Professores
            'professores.required' => 'Adicione pelo menos um professor ao projeto.',
            'professores.*.id.required' => 'Selecione um professor válido.',
            'professores.*.id.exists' => 'O professor selecionado não existe.',
            'professores.*.area.string' => 'A área deve ser um texto.',
    
            // Pareceres
            'numero_projeto.string' => 'O número do projeto deve ser um texto.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser sim ou não.',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser sim ou não.',
            'data_recebimento_napex.date' => 'A data de recebimento pelo NAPEx deve ser uma data válida.',
            'data_encaminhamento_parecer.date' => 'A data de encaminhamento para os pareceres deve ser uma data válida.',
            'data_parecer_coordenador.date' => 'A data do parecer do coordenador deve ser uma data válida.',
    
            // Arquivo
            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: jpeg, png, jpg, pdf, doc, docx.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',
    
            // Atividades
            'atividades.*.o_que_fazer.required' => 'A descrição do que fazer na atividade é obrigatória.',
            'atividades.*.como_fazer.required' => 'A descrição de como fazer na atividade é obrigatória.',
            'atividades.*.carga_horaria.required' => 'A carga horária da atividade é obrigatória.',
    
            // Cronograma
            'cronograma.*.atividade.required' => 'O nome da atividade no cronograma é obrigatório.',
            'cronograma.*.mes.required' => 'O mês da atividade no cronograma é obrigatório.',
            'cronograma.*.mes.in' => 'O mês deve ser entre Fevereiro e Novembro',

        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $datas = [
                'data_recebimento_napex' => $this->input('data_recebimento_napex'),
                'data_encaminhamento_parecer' => $this->input('data_encaminhamento_parecer'),
                'data_inicio' => $this->input('data_inicio'),
                'data_fim' => $this->input('data_fim'),
            ];

            foreach ($datas as $campo => $valor) {
                if ($valor && preg_match('/^(\d{4})-\d{2}-\d{2}$/', $valor, $matches)) {
                    $ano = (int)$matches[1];
                    if ($ano > 9999 || $ano < 1000) {
                        $validator->errors()->add($campo, 'O ano da data deve estar entre 1000 e 9999.');
                    }
                } elseif ($valor && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                    $validator->errors()->add($campo, 'A data deve estar no formato AAAA-MM-DD.');
                }
            }
        });
    }

    

    
}
