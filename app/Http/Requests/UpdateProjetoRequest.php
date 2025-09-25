<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjetoRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        // A autorização é tratada pela policy no controller, então aqui podemos retornar true.
        return true;
    }

    /**
     * Retorna as regras de validação que se aplicam à requisição.
     */
    public function rules(): array
    {
        $user = $this->user();
        $todosOsMeses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        // Regras base para Alunos e Professores que estão editando o projeto
        $baseRules = [
            'titulo' => ['required', 'string', 'max:255'],
            'periodo' => ['required', 'string', 'max:50'],
            'data_inicio' => ['required', 'date_format:Y-m-d'],
            'data_fim' => ['required', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
            'publico_alvo' => ['nullable', 'string', 'max:100'],
            'introducao' => ['nullable', 'string', 'max:1000'],
            'objetivo_geral' => ['nullable', 'string', 'max:1000'],
            'justificativa' => ['nullable', 'string', 'max:1000'],
            'metodologia' => ['nullable', 'string', 'max:500'],
            'recursos' => ['nullable', 'string', 'max:1000'],
            'resultados_esperados' => ['nullable', 'string', 'max:1000'],
            'arquivo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,doc,docx', 'max:5120'],
            'alunos' => ['nullable', 'array'],
            'alunos.*' => ['exists:users,id'],
            'professores' => ['nullable', 'array'],
            'professores.*' => ['exists:users,id'],
            'atividades' => ['required', 'array', 'min:1', 'max:10'],
            'atividades.*.o_que_fazer' => ['required', 'string', 'max:1000'],
            'atividades.*.como_fazer' => ['required', 'string', 'max:1000'],
            'atividades.*.carga_horaria' => ['required', 'integer', 'min:1', 'max:99999'],
            'cronograma' => ['required', 'array', 'min:1', 'max:10'],
            'cronograma.*.atividade' => ['required', 'string', 'max:100'],
            'cronograma.*.mes_inicio' => ['required', 'string', Rule::in($todosOsMeses)],
            'cronograma.*.mes_fim' => ['required', 'string', Rule::in($todosOsMeses)],
        ];
        
        // Regras para o Coordenador (apenas parecer)
        if (str_starts_with($user->role, 'coordenador')) {
            return [
                'aprovado_coordenador' => ['required', 'string', Rule::in(['sim', 'nao'])],
                'motivo_coordenador' => ['nullable', 'string', 'required_if:aprovado_coordenador,nao', 'max:2000'],
            ];
        }

        // Regras para o NAPEX (apenas parecer e número do projeto)
        if ($user->role === 'napex') {
            return [
                'numero_projeto' => ['nullable', 'string', 'max:255'],
                'aprovado_napex' => ['required', 'string', Rule::in(['sim', 'nao'])],
                'motivo_napex' => ['nullable', 'string', 'required_if:aprovado_napex,nao', 'max:2000'],
            ];
        }
        
        // Alunos e outros perfis usam as regras base
        return $baseRules;
    }

    /**
     * Retorna as mensagens de erro customizadas.
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do projeto é obrigatório.',
            'periodo.required' => 'O período do projeto é obrigatório.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de término é obrigatória.',
            'data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',

            'alunos.array' => 'O campo de alunos participantes deve ser uma lista.',
            'alunos.*.exists' => 'Um dos alunos selecionados é inválido.',
            'professores.array' => 'O campo de professores orientadores deve ser uma lista.',
            'professores.*.exists' => 'Um dos professores selecionados é inválido.',

            'aprovado_coordenador.required' => 'A decisão sobre a aprovação do coordenador é obrigatória.',
            'motivo_coordenador.required_if' => 'O motivo da reprovação pelo coordenador é obrigatório.',
            'aprovado_napex.required' => 'A decisão sobre a aprovação do NAPEX é obrigatória.',
            'motivo_napex.required_if' => 'O motivo da reprovação pelo NAPEX é obrigatório.',
        ];
    }
    
    /**
     * Configura o validador com lógica adicional.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
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