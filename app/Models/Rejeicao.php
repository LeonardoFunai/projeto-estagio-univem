<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rejeicao extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'motivo',
        'data_rejeicao',
        'autor',
    ];

    protected $table = 'rejeicoes'; 

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}
