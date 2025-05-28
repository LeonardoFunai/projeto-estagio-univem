<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjetoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = auth()->user()->role;
        $todosOsMeses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

        // ... (suas regras base e condicionais para roles) ...
        // Certifique-se que as regras estejam aqui conforme a sugestão anterior.
        // Vou copiar a estrutura que tínhamos para o baseRules e condicionais:
        $baseRules = [
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:255', // Ajuste max se necessário
            'data_inicio' => 'required|date_format:Y-m-d',
            'data_fim' => 'required|date_format:Y-m-d|after_or_equal:data_inicio',
            'professores' => 'nullable|array',
            'professores.*.id' => ['required_with:professores', Rule::exists('users', 'id')/*->where('tipo_usuario', 'professor')*/],
            'professores.*.area' => 'nullable|string|max:100',
            'alunos' => 'nullable|array',
            'alunos.*.nome' => 'required_with:alunos|string|max:100',
            'alunos.*.ra' => 'required_with:alunos|string|max:50',
            'alunos.*.curso' => 'required_with:alunos|string|max:100',
            'publico_alvo' => 'nullable|string|max:100',
            'introducao' => 'nullable|string|max:1000',
            'objetivo_geral' => 'nullable|string|max:1000',
            'justificativa' => 'nullable|string|max:1000',
            'metodologia' => 'nullable|string|max:500',
            'recursos' => 'nullable|string',
            'resultados_esperados' => 'nullable|string|max:1000',
            'arquivo' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',
            'atividades' => 'nullable|array',
            'atividades.*.o_que_fazer' => 'required_with:atividades|string|max:1000',
            'atividades.*.como_fazer' => 'required_with:atividades|string|max:1000',
            'atividades.*.carga_horaria' => 'required_with:atividades|integer|min:1|max:99999',
            'cronograma' => 'nullable|array',
            'cronograma.*.atividade' => 'required_with:cronograma|string|max:100',
            'cronograma.*.mes_inicio' => ['required_with:cronograma', 'string', Rule::in($todosOsMeses)],
            'cronograma.*.mes_fim' => ['required_with:cronograma', 'string', Rule::in($todosOsMeses)],
        ];

        if ($role === 'coordenador') {
            return [
                'aprovado_coordenador' => 'nullable|string|in:pendente,sim,nao',
                'motivo_coordenador' => 'nullable|string|max:1000',
                'data_parecer_coordenador' => 'nullable|date_format:Y-m-d', // Adicionei date_format
            ];
        }

        if ($role === 'napex') {
            return array_merge($baseRules, [
                'periodo' => 'required|string|max:50', // Sobrescreve para NAPEx
                'data_entrega' => ['nullable', 'date_format:Y-m-d'], // Adicionei date_format
                'data_parecer_napex' => ['nullable', 'date_format:Y-m-d'], // Adicionei date_format
                'numero_projeto' => 'nullable|string|max:255',
                'aprovado_napex' => 'nullable|string|in:pendente,sim,nao',
                'motivo_napex' => 'nullable|string|max:1000',
            ]);
        }
        return $baseRules;
    }

    public function messages(): array
    {
        // ... (seu método messages() atualizado da resposta anterior) ...
        return [
            'titulo.required' => 'O título do projeto é obrigatório.',
            'periodo.required' => 'O período do projeto é obrigatório.',
            'periodo.max' => 'O período do projeto não pode exceder :max caracteres.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date_format' => 'A data de início não está no formato válido (AAAA-MM-DD).',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.date_format' => 'A data de término não está no formato válido (AAAA-MM-DD).',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'professores.*.id.required_with' => 'Selecione um professor válido para cada entrada de professor.',
            'professores.*.id.exists' => 'O professor selecionado não existe ou não é válido.',
            'professores.*.area.max' => 'A área do professor deve ter no máximo 100 caracteres.',
            'alunos.*.nome.required_with' => 'O nome do aluno é obrigatório para cada entrada de aluno.',
            'alunos.*.ra.required_with' => 'O RA do aluno é obrigatório para cada entrada de aluno.',
            'alunos.*.curso.required_with' => 'O curso do aluno é obrigatório para cada entrada de aluno.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser "pendente", "sim" ou "não".',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser "pendente", "sim" ou "não".',
            'atividades.*.o_que_fazer.required_with' => 'A descrição "o que fazer" da atividade é obrigatória.',
            'atividades.*.como_fazer.required_with' => 'A descrição "como fazer" da atividade é obrigatória.',
            'atividades.*.carga_horaria.required_with' => 'A carga horária da atividade é obrigatória.',
            'atividades.*.carga_horaria.integer' => 'A carga horária da atividade deve ser um número inteiro.',
            'atividades.*.carga_horaria.min' => 'A carga horária da atividade deve ser no mínimo :min.',
            'cronograma.*.atividade.required_with' => 'O título da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_inicio.required_with' => 'O mês de início da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_inicio.in' => 'O mês de início selecionado para a atividade no cronograma é inválido.',
            'cronograma.*.mes_fim.required_with' => 'O mês de fim da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_fim.in' => 'O mês de fim selecionado para a atividade no cronograma é inválido.',
            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: jpeg, png, jpg, pdf, doc, docx.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',
            'data_parecer_coordenador.date_format' => 'A data do parecer do Coordenador não está no formato válido (AAAA-MM-DD).',
            'data_entrega.date_format' => 'A data de entrega (NAPEx) não está no formato válido (AAAA-MM-DD).',
            'data_parecer_napex.date_format' => 'A data do parecer (NAPEx) não está no formato válido (AAAA-MM-DD).',
        ];
    }

    public function attributes(): array
    {
        // ... (seu método attributes() completo da resposta anterior) ...
        return [
            'titulo' => 'título do projeto',
            'periodo' => 'período',
            'data_inicio' => 'data de início',
            'data_fim' => 'data de término',
            'professores.*.id' => 'professor responsável',
            'professores.*.area' => 'área do professor',
            'alunos.*.nome' => 'nome do aluno',
            'alunos.*.ra' => 'RA do aluno',
            'alunos.*.curso' => 'curso do aluno',
            'publico_alvo' => 'público alvo',
            'introducao' => 'introdução',
            'objetivo_geral' => 'objetivo geral',
            'justificativa' => 'justificativa',
            'metodologia' => 'metodologia',
            'recursos' => 'recursos necessários',
            'resultados_esperados' => 'resultados esperados',
            'arquivo' => 'arquivo anexo',
            'atividades.*.o_que_fazer' => 'descrição "o que fazer" da atividade',
            'atividades.*.como_fazer' => 'descrição "como fazer" da atividade',
            'atividades.*.carga_horaria' => 'carga horária da atividade',
            'cronograma.*.atividade' => 'título da atividade no cronograma',
            'cronograma.*.mes_inicio' => 'mês de início da atividade no cronograma',
            'cronograma.*.mes_fim' => 'mês de fim da atividade no cronograma',
            'aprovado_coordenador' => 'aprovação do coordenador',
            'motivo_coordenador' => 'motivo da decisão (coordenador)',
            'data_parecer_coordenador' => 'data do parecer (coordenador)',
            'data_entrega' => 'data de entrega (NAPEx)',
            'data_parecer_napex' => 'data do parecer (NAPEx)',
            'numero_projeto' => 'número do projeto (NAPEx)',
            'aprovado_napex' => 'aprovação do NAPEx',
            'motivo_napex' => 'motivo da decisão (NAPEx)',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Evita duplicidade de professores
            if ($this->has('professores') && is_array($this->input('professores'))) {
                $professoresIds = collect($this->input('professores'))->pluck('id')->filter();
                if ($professoresIds->count() > $professoresIds->unique()->count()) {
                    $validator->errors()->add('professores', 'Você não pode adicionar o mesmo professor mais de uma vez.');
                }
            }

            // Valida formato e se a data é válida para os campos de data principais
            $datasParaValidar = [
                'data_inicio' => $this->input('data_inicio'),
                'data_fim' => $this->input('data_fim'),
                // Adicione aqui as outras datas que foram migradas para 'date_format:Y-m-d' nas rules(),
                // se você ainda quiser a validação extra de checkdate e ano.
                // Ex: 'data_entrega' => $this->input('data_entrega'),
                //     'data_parecer_napex' => $this->input('data_parecer_napex'),
                //     'data_parecer_coordenador' => $this->input('data_parecer_coordenador'),
            ];

            // Pega os atributos customizados uma vez
            $customAttributes = $this->attributes();

            foreach ($datasParaValidar as $campo => $valor) {
                if ($valor) {
                    // Determina o nome amigável do campo usando isset() e ternário
                    $nomeAmigavelDoCampo = isset($customAttributes[$campo]) ? $customAttributes[$campo] : $campo;

                    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $matches)) {
                        // A regra 'date_format:Y-m-d' nas rules() já deve pegar isso, mas mantendo por segurança.
                        $validator->errors()->add($campo, "O campo {$nomeAmigavelDoCampo} deve estar no formato AAAA-MM-DD.");
                    } else {
                        $ano = (int)$matches[1];
                        $mes = (int)$matches[2];
                        $dia = (int)$matches[3];
                        if ($ano < 1900 || $ano > 2100) {
                            $validator->errors()->add($campo, "O ano em {$nomeAmigavelDoCampo} parece inválido (deve ser entre 1900 e 2100).");
                        } elseif (!checkdate($mes, $dia, $ano)) {
                            $validator->errors()->add($campo, "A data informada em {$nomeAmigavelDoCampo} não é válida (dia, mês ou ano incorretos).");
                        }
                    }
                }
            }

            // Validação para mes_fim ser >= mes_inicio no cronograma
            if ($this->has('cronograma') && is_array($this->input('cronograma'))) {
                $todosOsMesesArray = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                foreach ($this->input('cronograma') as $index => $item) {
                    $mesInicio = $item['mes_inicio'] ?? null;
                    $mesFim = $item['mes_fim'] ?? null;

                    if ($mesInicio && $mesFim) {
                        $indiceInicio = array_search($mesInicio, $todosOsMesesArray);
                        $indiceFim = array_search($mesFim, $todosOsMesesArray);

                        // Verifica se os meses foram encontrados no array e se o fim não é antes do início
                        if ($indiceInicio !== false && $indiceFim !== false && $indiceFim < $indiceInicio) {
                            // Nome amigável para o campo de erro específico
                            $nomeAmigavelMesFim = $customAttributes["cronograma.{$index}.mes_fim"] ?? "mês de fim da atividade ".($index + 1);
                            $validator->errors()->add("cronograma.{$index}.mes_fim", "O {$nomeAmigavelMesFim} não pode ser anterior ao mês de início.");
                        }
                    }
                }
            }
        });
    }
}