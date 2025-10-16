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
        $rules = [
            'atividades_desenvolvidas' => 'required|string|max:15000',
            'parceiro_organizacao' => 'nullable|string|max:255',
            'parceiro_responsavel' => 'nullable|string|max:255',
            'parceiro_endereco' => 'nullable|string|max:255',
            'parceiro_cnpj' => 'nullable|string|max:20',
            'parceiro_tipo_participacao' => 'nullable|string|max:255',
            'comunidade_externa' => 'nullable|string|max:5000',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['anexos'] = 'nullable|array';
            $rules['anexos.*.descricao'] = 'required_with:anexos|string|max:1000';
            $rules['anexos.*.arquivo'] = 'required_with:anexos|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,mp4,avi,mkv|max:20480';
        } else {
            $rules['anexos'] = 'required|array|min:1';
            $rules['anexos.*.descricao'] = 'required|string|max:1000';
            $rules['anexos.*.arquivo'] = 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,mp4,avi,mkv|max:20480';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'atividades_desenvolvidas.required' => 'O campo "Atividades Desenvolvidas" é obrigatório.',
            'atividades_desenvolvidas.max' => 'O campo "Atividades Desenvolvidas" não pode ter mais de :max caracteres.',

            'parcerias.max' => 'O campo de parcerias não pode ter mais de :max caracteres.',
            'comunidade_externa.max' => 'O campo "Comunidade Externa" não pode ter mais de :max caracteres.',

            // Mensagens para o novo sistema de anexos
            'anexos.required' => 'É necessário enviar pelo menos um anexo.',
            'anexos.min' => 'É necessário enviar pelo menos um anexo.',

            'anexos.*.descricao.required' => 'A descrição para cada anexo é obrigatória.',
            'anexos.*.descricao.max' => 'A descrição do anexo não pode ter mais de :max caracteres.',

            'anexos.*.arquivo.required' => 'O arquivo para cada anexo é obrigatório.',
            'anexos.*.arquivo.file' => 'O anexo enviado não é um arquivo válido.',
            'anexos.*.arquivo.mimes' => 'Cada anexo deve ser de um dos seguintes tipos: :values.',
            'anexos.*.arquivo.max' => 'Cada anexo não pode ser maior que 20MB.',
        ];
    }
}