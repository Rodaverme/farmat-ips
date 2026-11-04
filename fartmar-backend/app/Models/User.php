<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Rol obligatorio del usuario[cite: 4]
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

    // Helpers de verificación de roles (Sección 3)[cite: 4]
    public function esAuxiliar(): bool
    {
        return $this->role === 'auxiliar_farmacia';
    }

    public function esRegente(): bool
    {
        return $this->role === 'regente_farmacia';
    }

    public function esMedico(): bool
    {
        return $this->role === 'medico';
    }

    public function esAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function esAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
