<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    use HasFactory;

    protected $fillable = [
        'resultado_id',
        'nome_original',
        'path',
        'mime_type',
        'descricao',
    ];

    public function resultado()
    {
        return $this->belongsTo(Resultado::class);
    }
}