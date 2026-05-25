<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // Importante para o login funcionar

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Campos que podem ser preenchidos em massa.
     * Certifique-se de que esses nomes sejam IGUAIS aos da sua Migration.
     */
    protected $fillable = [
        'nome',      // Troquei 'name' por 'nome' para bater com seu padrão
        'usuario',   // Faltava aqui
        'email',
        'password',
        'biografia', // Faltava aqui
        'foto',      // Faltava aqui
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Métodos obrigatórios do JWT
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [
            'usuario' => $this->usuario,
        ];
    }
}