<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetoLog extends Model
{
    use HasFactory;

    protected $table = 'projeto_logs';

    protected $fillable = [
        'batch_id',
        'projeto_id',
        'user_id',
        'acao',
        'descricao',
        'dados_antigos',
        'dados_novos',
        'loggable_id',   
        'loggable_type',
    ];

    protected $casts = [
        'dados_antigos' => 'array',
        'dados_novos'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

        public function loggable()
    {
        return $this->morphTo();
    }
}