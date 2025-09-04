<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Resultado; 

class StoreResultadoRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        // Se a rota contém um objeto 'resultado', significa que estamos ATUALIZANDO (update).
        if ($resultado = $this->route('resultado')) {
            
            // O Laravel vai verificar se o usuário logado (aluno OU professor) pode atualizar este resultado.
            return $this->user()->can('update', $resultado);
        }

        // Se a rota contém um objeto 'projeto', significa que estamos CRIANDO (store).
        if ($projeto = $this->route('projeto')) {
            // Então, usamos a regra 'create' da nossa ResultadoPolicy.
            // O Laravel vai verificar se o usuário logado (aluno) pode criar um resultado para este projeto.
            return $this->user()->can('create', [Resultado::class, $projeto]);
        }

        // Medida de segurança: se nenhuma condição for atendida, nega o acesso.
        return false;
    }


    public function rules(): array
    {
        return [
            'atividades_desenvolvidas' => 'required|string',
            'comunidade_externa' => 'nullable|string',
            'parceiro_organizacao' => 'nullable|string|max:255',
            'parceiro_endereco' => 'nullable|string|max:255',
            'parceiro_cnpj' => 'nullable|string|max:20',
            'parceiro_responsavel' => 'nullable|string|max:255',
            'parceiro_tipo_participacao' => 'nullable|string|max:255',
            'anexos_descricao' => 'nullable|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:10240',
        ];
    }
}