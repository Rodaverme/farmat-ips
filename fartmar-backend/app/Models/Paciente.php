<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $tipo_documento
 * @property string $numero_documento
 * @property string $fecha_nacimiento
 * @property string $genero
 * @property string|null $telefono
 * @property string|null $direccion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

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

    /**
     * @return HasMany<Prescripcion, $this>
     */
    public function prescripciones(): HasMany
    {
        return $this->hasMany(Prescripcion::class);
    }

    /**
     * @return HasMany<Dispensacion, $this>
     */
    public function dispensaciones(): HasMany
    {
        return $this->hasMany(Dispensacion::class);
    }
}
