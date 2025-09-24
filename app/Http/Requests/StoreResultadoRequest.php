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

            'atividades_desenvolvidas' => 'required|string|max:1000', 
            'comunidade_externa' => 'nullable|string|max:1000',
            'anexos_descricao' => 'nullable|string|max:1000',


            'parceiro_organizacao' => 'nullable|string|max:255',
            'parceiro_responsavel' => 'nullable|string|max:255',
            'parceiro_endereco' => 'nullable|string|max:255',
            'parceiro_cnpj' => 'nullable|string|max:20',
            'parceiro_tipo_participacao' => 'nullable|string|max:255',
            
            'anexos' => 'nullable|array', 
            'anexos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,mp4,avi,mkv|max:20480', // 20MB
        ];
    }

    public function messages(): array
    {
        return [
            'atividades_desenvolvidas.required' => 'O campo "Atividades Desenvolvidas" é obrigatório.',
            'atividades_desenvolvidas.max' => 'O campo "Atividades Desenvolvidas" não pode ter mais de :max caracteres.',

            'comunidade_externa.max' => 'O campo "Comunidade Externa" não pode ter mais de :max caracteres.',

            'anexos_descricao.max' => 'A descrição dos anexos não pode ter mais de :max caracteres.',

            'anexos.*.file' => 'O anexo enviado não é um arquivo válido.',
            'anexos.*.mimes' => 'O anexo deve ser de um dos seguintes tipos: :values.',
            'anexos.*.max' => 'O anexo não pode ser maior que 20MB.',

            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto.',
            'max' => 'O campo :attribute excedeu o limite de caracteres.',
        ];
    }

}