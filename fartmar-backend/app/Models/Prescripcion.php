<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $paciente_id
 * @property int $medico_id
 * @property int $producto_id
 * @property int $cantidad_prescrita
 * @property int $cantidad_dispensada
 * @property string $fecha_prescripcion
 * @property string $diagnostico
 * @property string|null $observaciones
 * @property-read Producto $producto
 */
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
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
