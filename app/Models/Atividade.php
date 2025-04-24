<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'o_que_fazer',
        'como_fazer',
        'carga_horaria',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}
