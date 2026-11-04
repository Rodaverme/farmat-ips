<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\relations\HasMany;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'direccion',
    ];

    public function prescripciones(): HasMany
    {
        return $this->hasMany(Prescripcion::class);
    }

    public function dispensaciones(): HasMany
    {
        return $this->hasMany(Dispensacion::class);
    }

    //
}
