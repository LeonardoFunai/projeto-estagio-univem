<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'cpf',
        'ra',
        'data_nascimento',
        'curso_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'data_nascimento' => 'date',
        'password' => 'hashed',
    ];

    /**
     * Get the curso that the user belongs to.
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
        public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
    public function cursosCoordenados(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_user');
    }
}