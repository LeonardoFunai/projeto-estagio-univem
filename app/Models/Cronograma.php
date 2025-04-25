<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'atividade',
        'mes',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}

