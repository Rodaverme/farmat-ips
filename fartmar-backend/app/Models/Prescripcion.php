<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescripcion extends Model
{
    use HasFactory;

    protected $table = 'prescripciones';

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'producto_id',
        'cantidad_prescrita',
        'cantidad_dispensada',
        'fecha_prescripcion',
        'diagnostico',
        'observaciones',
    ];

    /**
     * Relación con el Paciente.
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con el Producto.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Relación con el Médico.
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}