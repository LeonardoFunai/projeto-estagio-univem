<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory;

    protected $table = 'professores';

    protected $fillable = [
        'projeto_id',
        'user_id',   
        'nome',
        'email',
        'area',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
