<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjetoRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        // Apenas usuários autenticados com a role 'aluno' podem criar projetos.
        return auth()->check() && auth()->user()->role === 'aluno';
    }

    /**
     * Retorna as regras de validação que se aplicam à requisição.
     */
    public function rules(): array
    {
        $cursoDoAutorId = auth()->user()->curso_id;
        $todosOsMeses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        return [
            // Dados do Projeto
            'titulo' => ['required', 'string', 'max:255'],
            'periodo' => ['required', 'string', 'max:50'],
            'data_inicio' => ['required', 'date_format:Y-m-d'],
            'data_fim' => ['required', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],

            // Descrição do Projeto
            'publico_alvo' => ['nullable', 'string', 'max:1000'],
            'introducao' => ['nullable', 'string', 'max:15000'],
            'objetivo_geral' => ['nullable', 'string', 'max:15000'],
            'justificativa' => ['nullable', 'string', 'max:15000'],
            'metodologia' => ['nullable', 'string', 'max:15000'],
            'recursos' => ['nullable', 'string', 'max:15000'],
            'resultados_esperados' => ['nullable', 'string', 'max:15000'],
            
            // Arquivo
            'arquivo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,doc,docx', 'max:5120'],

            // --- NOVAS REGRAS PARA ALUNOS E PROFESSORES ---
            'alunos' => 'nullable|array',
            'alunos.*' => [
                'integer',
                
                Rule::exists('users', 'id')->where(function ($query) use ($cursoDoAutorId) {
                    return $query->where('curso_id', $cursoDoAutorId);
                }),
        ],
            'professores' => ['nullable', 'array'],
            'professores.*' => ['exists:users,id'], // Valida se cada ID de professor existe na tabela 'users'

            // Atividades
            'atividades' => ['required', 'array', 'min:1', 'max:10'],
            'atividades.*.o_que_fazer' => ['required', 'string', 'max:15000'],
            'atividades.*.como_fazer' => ['required', 'string', 'max:15000'],
            'atividades.*.carga_horaria' => ['required', 'integer', 'min:1', 'max:99999'],

            // Cronograma
            'cronograma' => ['required', 'array', 'min:1', 'max:10'],
            'cronograma.*.atividade' => ['required', 'string', 'max:100'],
            'cronograma.*.mes_inicio' => ['required', 'string', Rule::in($todosOsMeses)],
            'cronograma.*.mes_fim' => ['required', 'string', Rule::in($todosOsMeses)],

            // Novas regras para os convites
            'invitations' => 'nullable|array',
            'invitations.*.email' => 'required|email',
            'invitations.*.role' => 'required|in:aluno,professor',
        ];
    }

    /**
     * Retorna as mensagens de erro customizadas.
     */
    public function messages(): array
    {
        return [
            // Mensagens para os campos principais do projeto...
            'titulo.required' => 'O título do projeto é obrigatório.',
            'periodo.required' => 'O período do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            // --- NOVAS MENSAGENS PARA ALUNOS E PROFESSORES ---
            'alunos.array' => 'O campo de alunos participantes deve ser uma lista.',
            'alunos.*.exists' => 'Um dos alunos selecionados é inválido.',
            'professores.array' => 'O campo de professores orientadores deve ser uma lista.',
            'professores.*.exists' => 'Um dos professores selecionados é inválido.',

            // Mensagens para atividades...
            'atividades.required' => 'Adicione pelo menos uma atividade ao projeto.',
            'atividades.*.o_que_fazer.required' => 'A descrição "O que fazer?" da atividade é obrigatória.',
            'atividades.*.como_fazer.required' => 'A descrição "Como fazer?" da atividade é obrigatória.',
            'atividades.*.carga_horaria.required' => 'A carga horária da atividade é obrigatória.',

            // Mensagens para o cronograma...
            'cronograma.required' => 'Adicione pelo menos uma atividade ao cronograma.',
            'cronograma.*.atividade.required' => 'O título da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_inicio.required' => 'O mês de início da atividade no cronograma é obrigatório.',
            'cronograma.*.mes_fim.required' => 'O mês de fim da atividade no cronograma é obrigatório.',
        ];
    }

    /**
     * Configura o validador com lógica adicional.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validação customizada para os meses do cronograma (mantida)
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

                        if ($indiceInicio !== null && $indiceFim !== null && $indiceFim < $indiceInicio) {
                            $validator->errors()->add(
                                "cronograma.{$index}.mes_fim",
                                "O 'Mês de Fim' da atividade '{$item['atividade']}' não pode ser anterior ao 'Mês de Início'."
                            );
                        }
                    }
                }
            }
        });
    }
}