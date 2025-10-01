<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    // Permite o uso de "factories" para testes e popular o banco (seeding)
    use HasFactory;

    /**
     * A propriedade $fillable define quais colunas da tabela 'cursos'
     * podem ser preenchidas em massa. Isso é uma proteção de segurança.
     * Neste caso, permitimos que o campo 'nome' seja preenchido.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
    ];

    /**
     * Define o relacionamento "um-para-muitos": um Curso pode ter muitos Alunos (Users).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alunos(): HasMany
    {
        // Alterado para apontar para User, assumindo que a lógica de alunos está no model User.
        return $this->hasMany(User::class, 'curso_id');
    }

    /**
     * Define o relacionamento "muitos-para-muitos": um Curso pode ter muitos Coordenadores (Users).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coordenadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'curso_user');
    }

    /**
     * ACCESSOR: Retorna o nome do curso de forma resumida (sigla).
     *
     * @return string
     */
    public function getNomeResumidoAttribute(): string
    {
        return match ($this->attributes['nome']) {
            'Análise e Desenvolvimento de Sistemas' => 'ADS',
            'Ciência da Computação' => 'CC',
            'Ciências Contábeis' => 'Contábeis',
            'Engenharia de Produção' => 'Produção',
            'Sistemas de Informação' => 'SI',
            'Direito' => 'Direito',
            'Administração' => 'ADM',
            default => $this->attributes['nome'], // Retorna o nome completo se não houver sigla
        };
    }
}