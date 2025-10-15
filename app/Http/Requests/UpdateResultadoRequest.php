<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResultadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A autorização é tratada pela policy no controller.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'atividades_desenvolvidas' => 'required|string|max:15000',
            'parceiro_organizacao' => 'nullable|string|max:255',
            'parceiro_responsavel' => 'nullable|string|max:255',
            'parceiro_endereco' => 'nullable|string|max:255',
            'parceiro_cnpj' => 'nullable|string|max:20',
            'parceiro_tipo_participacao' => 'nullable|string|max:255',
            'comunidade_externa' => 'nullable|string|max:5000',
            'anexos_a_deletar' => 'nullable|array',
            'anexos_a_deletar.*' => 'integer|exists:anexos,id',
            'anexos' => 'nullable|array',
            'anexos.*.descricao' => 'required_with:anexos.*.arquivo|string|max:255',
            'anexos.*.arquivo' => 'required_with:anexos.*.descricao|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:2048',
        ];
    }
}   