<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $traslado_id
 * @property int $lote_inventario_id
 * @property int $cantidad_solicitada
 * @property int $cantidad_despachada
 * @property int $cantidad_recibida
 * @property-read Traslado $traslado
 * @property-read LoteInventario $lote
 */
class TrasladoDetalle extends Model
{
    use HasFactory;

    protected $table = 'traslado_detalles';

    protected $fillable = [
        'traslado_id',
        'lote_inventario_id',
        'cantidad_solicitada',
        'cantidad_despachada',
        'cantidad_recibida',
    ];

    /**
     * @return BelongsTo<Traslado, $this>
     */
    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class);
    }

    /**
     * @return BelongsTo<LoteInventario, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteInventario::class, 'lote_inventario_id');
    }
}
