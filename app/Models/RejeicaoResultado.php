<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejeicaoResultado extends Model
{
    use HasFactory;

    // Define a tabela correta (opcional, mas boa prática)
    protected $table = 'rejeicao_resultados';

    protected $fillable = [
        'resultado_id',
        'user_id',
        'motivo',
    ];

    // Relação para buscar os dados do avaliador
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}