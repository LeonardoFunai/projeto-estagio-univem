<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Curso;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    private $cursos;

    public function __construct()
    {
        $this->cursos = Curso::pluck('id', 'nome')->toArray();
    }

    public function model(array $row)
    {
        $cursoId = $this->cursos[trim($row['curso'])] ?? null;

        if (!$cursoId) {
            return null;
        }

        // --- INÍCIO DA CORREÇÃO DEFINITIVA ---
        $dataNascimento = null;
        if (!empty($row['data_nascimento'])) {
            // Se o valor for numérico, trata como data do Excel.
            if (is_numeric($row['data_nascimento'])) {
                $dataNascimento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['data_nascimento'])->format('Y-m-d');
            } else {
                // Se for texto, tenta interpretar de forma flexível.
                try {
                    $dataNascimento = Carbon::parse($row['data_nascimento'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $dataNascimento = null;
                }
            }
        }
        // --- FIM DA CORREÇÃO ---

        return new User([
            'name'     => $row['nome'],
            'email'    => $row['email'],
            'cpf'      => $row['cpf'] ?? null,
            'ra'       => $row['ra'],
            'data_nascimento' => $dataNascimento,
            'curso_id' => $cursoId,
            'role'     => 'aluno',
            'password' => Hash::make($row['senha']),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'ra' => 'required|unique:users,ra',
            'senha' => 'required|min:8',
            'curso' => 'required|string',
            'cpf' => 'nullable|unique:users,cpf',
            'data_nascimento' => 'nullable', // Removida a regra 'date' para permitir a conversão manual
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.unique' => 'O e-mail :input já existe no sistema.',
            'ra.unique' => 'O R.A. :input já existe no sistema.',
            'cpf.unique' => 'O CPF :input já existe no sistema.',
        ];
    }
}