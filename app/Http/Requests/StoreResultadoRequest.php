<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Resultado; 

class StoreResultadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        if ($resultado = $this->route('resultado')) {
            return $this->user()->can('update', $resultado);
        }
        if ($projeto = $this->route('projeto')) {
            return $this->user()->can('create', [Resultado::class, $projeto]);
        }
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
            'anexos' => 'nullable|array',
            'anexos.*' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,mov,avi,wmv',
                'max:20480',
            ],
        ];
    }
}