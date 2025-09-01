<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'projeto_id',
        'atividades_desenvolvidas',
        'comunidade_externa',
        'parceiro_organizacao',
        'parceiro_endereco',
        'parceiro_cnpj',
        'parceiro_responsavel',
        'parceiro_tipo_participacao',
        'anexos_descricao',
        'fotos_paths',
        'links_videos', 
        'status',

        
        'aprovado_napex',
        'parecer_napex',
        'aprovado_coordenador',
        'parecer_coordenador',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fotos_paths' => 'array', 
    ];

    /**
     * Get the projeto that owns the resultado.
     */
    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
    public function rejeicoes()
    {
        return $this->hasMany(RejeicaoResultado::class);
    }
}