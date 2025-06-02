<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importe esta classe

class UpdateProjetoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A autorização deve verificar se o usuário tem permissão para atualizar ESTE projeto.
        // Por exemplo, se é o dono, ou um professor vinculado, ou um admin/napex/coordenador.
        // O ID do projeto pode ser acessado via $this->route('projeto').
        // Exemplo:
        // $projeto = $this->route('projeto'); // Supondo que a rota use '{projeto}'
        // $user = auth()->user();
        // return $user->id === $projeto->user_id || $user->isAdmin(); // Adicione lógica de permissão aqui
        // Por enquanto, mantendo true, mas ajuste conforme sua lógica de permissão.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = auth()->user()->role;
        $todosOsMeses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        // Regras base aplicáveis a todos os usuários que podem editar
        $baseRules = [
            'titulo' => 'required|string|max:255',
            'periodo' => 'required|string|max:50', 
            'data_inicio' => 'required|date_format:Y-m-d',
            'data_fim' => 'required|date_format:Y-m-d|after_or_equal:data_inicio',

            // Professores
            // required_without_all:alunos,atividades,cronograma é um exemplo se você quiser que PELO MENOS UM grupo seja preenchido
            'professores' => 'nullable|array|min:0|max:9', 
            'professores.*.id' => 'required|integer|exists:users,id',
            'professores.*.area' => 'nullable|string|max:100',

            // Alunos
            'alunos' => 'nullable|array|min:0|max:9', // Pode ser nulo se não houver alunos
            'alunos.*.nome' => 'required|string|max:100',
            'alunos.*.ra' => 'required|string|max:50',
            'alunos.*.curso' => 'required|string|max:100',

            // Descrição do Projeto com limites definidos
            'publico_alvo' => 'nullable|string|max:100',
            'introducao' => 'nullable|string|max:1000',
            'objetivo_geral' => 'nullable|string|max:1000',
            'justificativa' => 'nullable|string|max:1000',
            'metodologia' => 'nullable|string|max:500',
            'recursos' => 'nullable|string|max:1000', 
            'resultados_esperados' => 'nullable|string|max:1000',



            // Atividades (texto e carga horária)
            'atividades' => 'nullable|array|min:0|max:10', // Pode ser nulo
            'atividades.*.o_que_fazer' => 'required|string|max:1000',
            'atividades.*.como_fazer' => 'required|string|max:1000',
            'atividades.*.carga_horaria' => 'required|integer|min:1|max:99999',

            // Cronograma
            'cronograma' => 'nullable|array|min:0|max:10', // Pode ser nulo
            'cronograma.*.atividade' => 'required|string|max:100',
            'cronograma.*.mes_inicio' => ['required', 'string', Rule::in($todosOsMeses)],
            'cronograma.*.mes_fim' => ['required', 'string', Rule::in($todosOsMeses)],

            // Campos de parecer (sempre nullable na base)
            'numero_projeto' => 'nullable|string|max:255',
            'aprovado_napex' => 'nullable|string|in:sim,nao,pendente',
            'motivo_napex' => 'nullable|string|max:2000',
            'data_parecer_napex' => 'nullable|date_format:Y-m-d',
            'aprovado_coordenador' => 'nullable|string|in:sim,nao,pendente',
            'motivo_coordenador' => 'nullable|string|max:2000',
            'data_parecer_coordenador' => 'nullable|date_format:Y-m-d',
        ];

        // Regras condicionais baseadas na role
        if ($role === 'coordenador') {
            // Coordenadores podem alterar apenas seus campos de parecer
            // e os campos do projeto não são 'required' ou 'disabled' se eles não estiverem preenchendo.
            // Se o objetivo é que eles *apenas* preencham parecer, restrinja mais os campos.
            return [
                'aprovado_coordenador' => 'required|string|in:sim,nao', // Coordenador DEVE informar
                'motivo_coordenador' => 'nullable|string|required_if:aprovado_coordenador,nao|max:2000',
                'data_parecer_coordenador' => 'required|date_format:Y-m-d', // Coordenador DEVE informar
            ];
        }

        if ($role === 'napex') {
            // NAPEx pode alterar seus campos de parecer
            return [
                'numero_projeto' => 'nullable|string|max:255',
                'aprovado_napex' => 'required|string|in:sim,nao', // NAPEx DEVE informar
                'motivo_napex' => 'nullable|string|required_if:aprovado_napex,nao|max:2000',
                'data_parecer_napex' => 'required|date_format:Y-m-d', // NAPEx DEVE informar
            ];
        }

        // Para outras roles (aluno, professor), retornam as regras base.
        // O aluno deve ter permissão para editar os campos base.
        // O professor pode ter restrições parecidas com o aluno ou mais flexíveis, dependendo da sua regra de negócio.
        // Se professores *não* podem editar nada além do parecer, use a lógica similar a coordenador/napex
        // para a role 'professor'.
        return $baseRules;
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
            'professores.required' => 'Adicione pelo menos um professor ao projeto.', // Se 'required' para base
            'professores.min' => 'É obrigatório adicionar pelo menos :min professor(es).',
            'professores.max' => 'Você pode adicionar no máximo :max professor(es).',
            'professores.*.id.required' => 'Selecione um professor válido para cada entrada de professor.', // Usado com 'required_with:professores'
            'professores.*.id.integer' => 'O ID do professor deve ser um número inteiro.',
            'professores.*.id.exists' => 'O professor selecionado não existe.',
            'professores.*.area.string' => 'A área do professor deve ser um texto.',
            'professores.*.area.max' => 'A área do professor não pode ter mais de :max caracteres.',

            // Alunos
            'alunos.required' => 'Adicione pelo menos um aluno ao projeto.', // Se 'required' para base
            'alunos.min' => 'É obrigatório adicionar pelo menos :min aluno(s).',
            'alunos.max' => 'Você pode adicionar no máximo :max aluno(s).',
            'alunos.*.nome.required' => 'O nome do aluno é obrigatório.', // Usado com 'required_with:alunos'
            'alunos.*.nome.max' => 'O nome do aluno não pode ter mais de :max caracteres.',
            'alunos.*.ra.required' => 'O RA do aluno é obrigatório.', // Usado com 'required_with:alunos'
            'alunos.*.ra.max' => 'O RA do aluno não pode ter mais de :max caracteres.',
            'alunos.*.curso.required' => 'O curso do aluno é obrigatório.', // Usado com 'required_with:alunos'
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
            'aprovado_napex.required' => 'A aprovação do NAPEx é obrigatória.',
            'aprovado_napex.in' => 'A aprovação do NAPEx deve ser "sim" ou "não".',
            'motivo_napex.required_if' => 'O motivo da reprovação pelo NAPEx é obrigatório.',
            'motivo_napex.max' => 'O motivo do parecer do NAPEx não pode ter mais de :max caracteres.',
            'data_parecer_napex.required' => 'A data do parecer do NAPEx é obrigatória.',
            'data_parecer_napex.date_format' => 'A data do parecer do NAPEx deve estar no formato AAAA-MM-DD.',
            'aprovado_coordenador.required' => 'A aprovação do Coordenador é obrigatória.',
            'aprovado_coordenador.in' => 'A aprovação do Coordenador deve ser "sim" ou "não".',
            'motivo_coordenador.required_if' => 'O motivo da reprovação pelo Coordenador é obrigatório.',
            'motivo_coordenador.max' => 'O motivo do parecer do Coordenador não pode ter mais de :max caracteres.',
            'data_parecer_coordenador.required' => 'A data do parecer do Coordenador é obrigatória.',
            'data_parecer_coordenador.date_format' => 'A data do parecer do Coordenador deve estar no formato AAAA-MM-DD.',

            // Arquivo
            'arquivo.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'arquivo.mimes' => 'O arquivo deve ser nos formatos: JPEG, PNG, JPG, PDF, DOC ou DOCX.',
            'arquivo.max' => 'O arquivo não pode ultrapassar 5MB.',

            // Atividades
            'atividades.required' => 'Adicione pelo menos uma atividade ao projeto.', // Se 'required' para base
            'atividades.min' => 'É obrigatório adicionar pelo menos :min atividade(s).',
            'atividades.max' => 'Você pode adicionar no máximo :max atividade(s).',
            'atividades.*.o_que_fazer.required' => 'A descrição "O que fazer?" da atividade é obrigatória.', // Usado com 'required_with:atividades'
            'atividades.*.o_que_fazer.max' => 'A descrição "O que fazer?" não pode ter mais de :max caracteres.',
            'atividades.*.como_fazer.required' => 'A descrição "Como fazer?" da atividade é obrigatória.', // Usado com 'required_with:atividades'
            'atividades.*.como_fazer.max' => 'A descrição "Como fazer?" não pode ter mais de :max caracteres.',
            'atividades.*.carga_horaria.required' => 'A carga horária da atividade é obrigatória.', // Usado com 'required_with:atividades'
            'atividades.*.carga_horaria.integer' => 'A carga horária da atividade deve ser um número inteiro.',
            'atividades.*.carga_horaria.min' => 'A carga horária da atividade deve ser de no mínimo :min hora(s).',
            'atividades.*.carga_horaria.max' => 'A carga horária da atividade não pode ser maior que :max horas.',

            // Cronograma
            'cronograma.required' => 'Adicione pelo menos uma atividade ao cronograma.', // Se 'required' para base
            'cronograma.min' => 'É obrigatório adicionar pelo menos :min atividade(s) ao cronograma.',
            'cronograma.max' => 'Você pode adicionar no máximo :max atividade(s) ao cronograma.',
            'cronograma.*.atividade.required' => 'O título da atividade no cronograma é obrigatório.', // Usado com 'required_with:cronograma'
            'cronograma.*.atividade.max' => 'O título da atividade no cronograma não pode ter mais de :max caracteres.',
            'cronograma.*.mes_inicio.required' => 'O mês de início da atividade no cronograma é obrigatório.', // Usado com 'required_with:cronograma'
            'cronograma.*.mes_inicio.in' => 'O mês de início selecionado para a atividade no cronograma é inválido.',
            'cronograma.*.mes_fim.required' => 'O mês de fim da atividade no cronograma é obrigatório.', // Usado com 'required_with:cronograma'
            'cronograma.*.mes_fim.in' => 'O mês de fim selecionado para a atividade no cronograma é inválido.',
        ];
    }

    /**
     * Define os nomes amigáveis dos atributos para as mensagens de erro.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
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
            'data_entrega' => 'data de entrega',
            'data_parecer_napex' => 'data do parecer (NAPEx)',
            'numero_projeto' => 'número do projeto (NAPEx)',
            'aprovado_napex' => 'aprovação do NAPEx',
            'motivo_napex' => 'motivo da decisão (NAPEx)',
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

                    // Apenas valida se ambos os meses foram fornecidos e são strings
                    if (is_string($mesInicio) && is_string($mesFim)) {
                        $indiceInicio = $monthOrderMap[$mesInicio] ?? null;
                        $indiceFim = $monthOrderMap[$mesFim] ?? null;

                        // Se os meses foram encontrados no mapa e o índice do mês de fim é menor que o de início
                        if ($indiceInicio !== null && $indiceFim !== null && $indiceFim < $indiceInicio) {
                            $validator->errors()->add(
                                "cronograma.{$index}.mes_fim",
                                "O 'Mês de Fim' da atividade '{$item['atividade']}' no cronograma não pode ser anterior ao 'Mês de Início'."
                            );
                        }
                    }
                }
            }
        });
    }
}