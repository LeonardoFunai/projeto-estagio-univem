<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    // 1. Permite o uso de "factories" para testes e popular o banco (seeding)
    use HasFactory;

    /**
     * 2. A propriedade $fillable define quais colunas da tabela 'cursos' 
     * podem ser preenchidas em massa. Isso é uma proteção de segurança.
     * Neste caso, permitimos que o campo 'nome' seja preenchido.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
    ];

    /**
     * 3. Define o relacionamento "um-para-muitos": um Curso pode ter muitos Alunos.
     * Esta função deve estar DENTRO da classe.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'curso_id');
    }
    public function coordenadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'curso_user');
    }
}