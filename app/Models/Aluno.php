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
        'curso',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}
