<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescripcion extends Model
{
    use HasFactory;

    // Nombre explícito de la tabla en español
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

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
