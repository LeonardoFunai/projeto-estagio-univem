<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetoInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'user_id', // ID de quem convidou
        'email',
        'role',
        'token',
        'status',
    ];

    /**
     * Pega o projeto associado ao convite.
     */
    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    /**
     * Pega o usuário que enviou o convite.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}