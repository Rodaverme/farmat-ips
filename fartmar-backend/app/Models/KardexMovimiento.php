<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bodega_id
 * @property int $producto_id
 * @property string $tipo_movimiento
 * @property int $cantidad
 * @property int $saldo_anterior
 * @property int $saldo_nuevo
 * @property string|null $referencia
 * @property string|null $observaciones
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class KardexMovimiento extends Model
{
    use HasFactory;

    protected $table = 'kardex_movimientos';

    protected $fillable = [
        'bodega_id',
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'saldo_anterior',
        'saldo_nuevo',
        'referencia',
        'observaciones',
    ];

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * @return BelongsTo<Bodega, $this>
     */
    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }
}
