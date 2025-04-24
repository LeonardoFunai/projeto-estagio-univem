<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory;

    protected $table = 'professores'; // 👈 Aqui está a correção!

    protected $fillable = [
        'projeto_id',
        'nome',
        'email',
        'area',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}
