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
        // Se a rota for para 'store' (criar um novo resultado)
        if ($this->routeIs('resultados.store')) {
            $projeto = $this->route('projeto');
            // Permite apenas se o usuário logado for o dono do projeto.
            return $this->user()->id === $projeto->user_id;
        }

        // Se a rota for para 'update' (atualizar um resultado existente)
        if ($this->routeIs('resultados.update')) {
            $resultado = $this->route('resultado');
            // Permite apenas se o usuário for o dono E o status permitir a edição.
            return $this->user()->id === $resultado->projeto->user_id &&
                   in_array($resultado->status, ['rascunho', 'reprovado']);
        }

        return false;
    }

    /**
     * Pega as regras de validação que se aplicam à requisição.
     */
    public function rules(): array
    {
        // Estas são as mesmas regras que tínhamos no controller.
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