<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'projeto_id',
        'atividade',
        'mes_inicio', // MODIFICADO: antigo 'mes' agora é 'mes_inicio'
        'mes_fim',    // ADICIONADO: novo campo para o mês de fim
        // Adicione aqui quaisquer outros campos que seu cronograma possa ter e que devam ser preenchíveis
    ];

    /**
     * Get the projeto that owns the cronograma.
     */
    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id'); // Boa prática especificar a chave estrangeira
    }
}