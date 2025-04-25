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
        'titulo',
        'periodo',
        'data_inicio',
        'data_fim',
        'publico_alvo',
        'introducao',
        'objetivo_geral',
        'justificativa',
        'metodologia',
        'recursos',
        'resultados_esperados',
        'numero_projeto',
        'data_recebimento_napex',
        'data_encaminhamento_parecer',
        'aprovado_napex',
        'motivo_napex',
        'aprovado_coordenador',
        'motivo_coordenador',
        'data_parecer_coordenador',
        'status',
        'arquivo'
    ];
    
    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }
    
    public function professores()
    {
        return $this->hasMany(Professor::class);
    }
    
    public function atividades()
    {
        return $this->hasMany(Atividade::class);
    }
    
    public function cronogramas()
    {
        return $this->hasMany(Cronograma::class);
    }
    
}
