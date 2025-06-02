<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 

class StoreProjetoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se apenas alunos podem criar projetos, mantenha esta linha.
        // Caso contrário, se qualquer usuário autenticado pode criar, mude para 'true'.
        return auth()->check() && auth()->user()->role === 'aluno';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $todosOsMeses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        return [
            // Campos de Data Gerais (formatos e nulabilidade)
            'data_entrega' => ['nullable', 'date_format:Y-m-d'],
            'data_parecer_napex' => ['nullable', 'date_format:Y-m-d'],
            'data_parecer_coordenador' => ['nullable', 'date_format:Y-m-d'],

            // Dados do Projeto
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:50',
            'data_inicio' => 'required|date_format:Y-m-d',
            'data_fim' => 'required|date_format:Y-m-d|after_or_equal:data_inicio',

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
            'recursos' => 'nullable|string|max:1000', // Adicionei max:1000 aqui
            'resultados_esperados' => 'nullable|string|max:1000',

            // Pareceres NAPEx e Coordenador (normalmente não seriam preenchidos na CRIAÇÃO)
            // Mantidos 'nullable' para não causar erro se o formulário enviar, mas o controle
            // de quem pode preencher isso deve estar no Controller e/ou na view.
            'numero_projeto' => 'nullable|string|max:255',
            'aprovado_napex' => 'nullable|string|in:sim,nao,pendente', // Adicionado 'pendente'
            'motivo_napex' => 'nullable|string|max:2000', // Aumentado max
            'aprovado_coordenador' => 'nullable|string|in:sim,nao,pendente', // Adicionado 'pendente'
            'motivo_coordenador' => 'nullable|string|max:2000', // Aumentado max

            // Arquivo
            'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120', // 5MB

            // Atividades (texto e carga horária)
            'atividades' => 'required|array|min:1|max:10',
            'atividades.*.o_que_fazer' => 'required|string|max:1000',
            'atividades.*.como_fazer' => 'required|string|max:1000',
            'atividades.*.carga_horaria' => 'required|integer|min:1|max:99999',

            // Cronograma
            'cronograma' => 'required|array|min:1|max:10',
            'cronograma.*.atividade' => 'required|string|max:100',
            'cronograma.*.mes_inicio' => ['required', 'string', Rule::in($todosOsMeses)],
            'cronograma.*.mes_fim' => ['required', 'string', Rule::in($todosOsMeses)],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Projeto
            'titulo.required' => 'O título do projeto é obrigatório.',
            'titulo.max' => 'O título do projeto não pode ter mais de :max caracteres.',
            'periodo.required' => 'O período do projeto é obrigatório.',
            'periodo.max' => 'O período do projeto não pode ter mais de :max caracteres.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date_format' => 'A data de início deve estar no formato AAAA-MM-DD.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.date_format' => 'A data de término deve estar no formato AAAA-MM-DD.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            // Professores
            'professores.required' => 'Adicione pelo menos um professor ao projeto.',
            'professores.min' => 'É obrigatório adicionar pelo menos :min professor(es).',
            'professores.max' => 'Você pode adicionar no máximo :max professor(es).',
            'professores.*.id.required' => 'Selecione um professor válido para cada entrada de professor.',
            'professores.*.id.integer' => 'O ID do professor deve ser um número inteiro.',
            'professores.*.id.exists' => 'O professor selecionado não existe.',
            'professores.*.area.string' => 'A área do professor deve ser um texto.',
            'professores.*.area.max' => 'A área do professor não pode ter mais de :max caracteres.',

            // Alunos
            'alunos.required' => 'Adicione pelo menos um aluno ao projeto.',
            'alunos.min' => 'É obrigatório adicionar pelo menos :min aluno(s).',
            'alunos.max' => 'Você pode adicionar no máximo :max aluno(s).',
            'alunos.*.nome.required' => 'O nome do aluno é obrigatório.',
            'alunos.*.nome.max' => 'O nome do aluno não pode ter mais de :max caracteres.',
            'alunos.*.ra.required' => 'O RA do aluno é obrigatório.',
            'alunos.*.ra.max' => 'O RA do aluno não pode ter mais de :max caracteres.',
            'alunos.*.curso.required' => 'O curso do aluno é obrigatório.',
            'alunos.*.curso.max' => 'O curso do aluno não pode ter mais de :max caracteres.',

            // Descrição do Projeto
            'publico_alvo.max' => 'O campo "Público Alvo" não pode ter mais de :max caracteres.',
            'introducao.max' => 'O campo "Introdução" não pode ter mais de :max caracteres.',
            'objetivo_geral.max' => 'O campo "Objetivos do Projeto" não pode ter mais de :max caracteres.',
            'justificativa.max' => 'O campo "Justificativa" não pode ter mais de :max caracteres.',
            'metodologia.max' => 'O campo "Metodologia" não pode ter mais de :max caracteres.',
            'recursos.max' => 'O campo "Recursos Necessários" não pode ter mais de :max caracteres.',
            'resultados_esperados.max' => 'O campo "Resultados Esperados" não pode ter mais de :max caracteres.',

            // Pareceres
            'numero_projeto.max' => 'O número do projeto não pode ter mais de :max caracteres.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser "sim", "não" ou "pendente".',
            'motivo_napex.max' => 'O motivo do parecer do NAPEx não pode ter mais de :max caracteres.',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser "sim", "não" ou "pendente".',
            'motivo_coordenador.max' => 'O motivo do parecer do Coordenador não pode ter mais de :max caracteres.',

            // Arquivo
            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: JPEG, PNG, JPG, PDF, DOC ou DOCX.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',

            // Atividades
            'atividades.required' => 'Adicione pelo menos uma atividade ao projeto.',
            'atividades.min' => 'É obrigatório adicionar pelo menos :min atividade(s).',
            'atividades.max' => 'Você pode adicionar no máximo :max atividade(s).',
            'atividades.*.o_que_fazer.required' => 'A descrição "O que fazer?" da atividade é obrigatória.',
            'atividades.*.o_que_fazer.max' => 'A descrição "O que fazer?" não pode ter mais de :max caracteres.',
            'atividades.*.como_fazer.required' => 'A descrição "Como fazer?" da atividade é obrigatória.',
            'atividades.*.como_fazer.max' => 'A descrição "Como fazer?" não pode ter mais de :max caracteres.',
            'atividades.*.carga_horaria.required' => 'A carga horária da atividade é obrigatória.',
            'atividades.*.carga_horaria.integer' => 'A carga horária da atividade deve ser um número inteiro.',
            'atividades.*.carga_horaria.min' => 'A carga horária da atividade deve ser de no mínimo :min hora(s).',
            'atividades.*.carga_horaria.max' => 'A carga horária da atividade não pode ser maior que :max horas.',

            // Cronograma
            'cronograma.required' => 'Adicione pelo menos uma atividade ao cronograma.',
            'cronograma.min' => 'É obrigatório adicionar pelo menos :min atividade(s) ao cronograma.',
            'cronograma.max' => 'Você pode adicionar no máximo :max atividade(s) ao cronograma.',
            'cronograma.*.atividade.required' => 'O título da atividade no cronograma é obrigatório.',
            'cronograma.*.atividade.max' => 'O título da atividade no cronograma não pode ter mais de :max caracteres.',
            'cronograma.*.mes_inicio.required' => 'O mês de início da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_inicio.in' => 'O mês de início selecionado para a atividade no cronograma é inválido.',
            'cronograma.*.mes_fim.required' => 'O mês de fim da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_fim.in' => 'O mês de fim selecionado para a atividade no cronograma é inválido.',

            // Datas de Parecer
            'data_entrega.date_format' => 'A data de entrega deve estar no formato AAAA-MM-DD.',
            'data_parecer_napex.date_format' => 'A data do parecer do NAPEx deve estar no formato AAAA-MM-DD.',
            'data_parecer_coordenador.date_format' => 'A data do parecer do Coordenador deve estar no formato AAAA-MM-DD.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validação de duplicidade de professores
            if ($this->has('professores') && is_array($this->input('professores'))) {
                $professoresIds = collect($this->input('professores'))->pluck('id')->filter();
                if ($professoresIds->count() > $professoresIds->unique()->count()) {
                    $validator->errors()->add('professores', 'Você não pode adicionar o mesmo professor mais de uma vez.');
                }
            }

            // Validação customizada para os meses do cronograma
            if ($this->has('cronograma') && is_array($this->input('cronograma'))) {
                $monthOrderMap = [
                    'Janeiro' => 0, 'Fevereiro' => 1, 'Março' => 2, 'Abril' => 3, 'Maio' => 4, 'Junho' => 5,
                    'Julho' => 6, 'Agosto' => 7, 'Setembro' => 8, 'Outubro' => 9, 'Novembro' => 10, 'Dezembro' => 11
                ];

                foreach ($this->input('cronograma') as $index => $item) {
                    $mesInicio = $item['mes_inicio'] ?? null;
                    $mesFim = $item['mes_fim'] ?? null;

                    if ($mesInicio && $mesFim) {
                        $indiceInicio = $monthOrderMap[$mesInicio] ?? null;
                        $indiceFim = $monthOrderMap[$mesFim] ?? null;

                        // Se os meses são válidos e o mês de fim é anterior ao mês de início
                        if ($indiceInicio !== null && $indiceFim !== null && $indiceFim < $indiceInicio) {
                            $validator->errors()->add(
                                "cronograma.{$index}.mes_fim",
                                "O 'Mês de Fim' da atividade no cronograma ({$item['atividade']}) não pode ser anterior ao 'Mês de Início'."
                            );
                        }
                    }
                }
            }

            // A validação de formato e ano das datas (data_inicio, data_fim) já é coberta por 'date_format:Y-m-d'
            // nas regras e pela validação 'after_or_equal'. Recomendo remover o loop 'datasParaValidar'
            // do método withValidator para evitar redundância, a menos que você tenha uma necessidade muito específica
            // de validar checkdate ou anos fora de 1900-2100 (o que geralmente é tratado por um bom DatePicker ou backend).
            // Se você quiser manter a validação de range de ano (1900-2100) ou checkdate, você pode fazer assim:
            // $datasParaValidarManualmente = [
            //     'data_inicio', 'data_fim', 'data_entrega', 'data_parecer_napex', 'data_parecer_coordenador'
            // ];
            // foreach ($datasParaValidarManualmente as $campo) {
            //     $valor = $this->input($campo);
            //     if ($valor && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $matches)) {
            //         $ano = (int)$matches[1];
            //         $mes = (int)$matches[2];
            //         $dia = (int)$matches[3];
            //         if ($ano < 1900 || $ano > 2100) {
            //             $validator->errors()->add($campo, "O ano em {$this->attributes()[$campo]} parece inválido (deve ser entre 1900 e 2100).");
            //         } elseif (!checkdate($mes, $dia, $ano)) {
            //             $validator->errors()->add($campo, "A data informada em {$this->attributes()[$campo]} não é válida (dia, mês ou ano incorretos).");
            //         }
            //     }
            // }
        });
    }
}