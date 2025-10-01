<?php

namespace App\Models;

use App\Traits\LogaAlteracoes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Projeto extends Model
{
    use HasFactory, LogaAlteracoes;

    /**
     * The attributes that are mass assignable.
     */
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
        'data_entrega',
        'data_parecer_napex',
        'aprovado_napex',
        'motivo_napex',
        'aprovado_coordenador',
        'motivo_coordenador',
        'data_parecer_coordenador',
        'status',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data_entrega' => 'datetime',
        'data_parecer_napex' => 'datetime',
        'data_parecer_coordenador' => 'datetime',
    ];

    /**
     * O usuário (criador) que este projeto pertence.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Os usuários (alunos e professores) que participam deste projeto.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projeto_user');
    }
    
    /**
     * As atividades associadas a este projeto.
     */
    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    /**
     * Os itens de cronograma associados a este projeto.
     */
    public function cronogramas(): HasMany
    {
        return $this->hasMany(Cronograma::class);
    }

    /**
     * As rejeições associadas a este projeto.
     */
    public function rejeicoes(): HasMany
    {
        return $this->hasMany(Rejeicao::class);
    }

    /**
     * O resultado associado a este projeto.
     */
    public function resultado(): HasOne
    {
        return $this->hasOne(Resultado::class);
    }

    /**
     * Todos os logs de histórico associados a este projeto.
     */
    public function todosOsLogs(): HasMany
    {
        return $this->hasMany(ProjetoLog::class)->latest();
    }

        /**
     * Define a relação para buscar apenas os participantes que são ALUNOS.
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projeto_user')->where('role', 'aluno');
    }

    /**
     * Define a relação para buscar apenas os participantes que são PROFESSORES.
     */
    public function professores(): BelongsToMany
        {
            return $this->belongsToMany(User::class, 'projeto_user')
                        ->where(function ($query) {
                            $query->where('role', 'like', 'professor%')
                                ->orWhere('role', 'like', 'coordenador%');
                        });
        }
}