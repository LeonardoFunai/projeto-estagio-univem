<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Professor;
use App\Models\Aluno;
use App\Models\Atividade;

class Projeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'titulo',
        'periodo',
        'data_inicio',
        'data_fim',
        'publico_alvo',
        'introducao',
        'objetivo_geral',
        'justificativa',
        'metodologia',
        'execucao_projeto',
        'documentacao_execucao',
        'relatorio_final',
        'cronograma',
        'recursos',
        'resultados_esperados',
        'arquivo',
    ];

    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }

    public function atividades()
    {
        return $this->hasMany(Atividade::class);
    }

    public function professores()
    {
        return $this->hasMany(Professor::class);
    }
}
