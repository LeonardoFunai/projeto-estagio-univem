<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'nome',
        'ra',
        'curso_id',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}
