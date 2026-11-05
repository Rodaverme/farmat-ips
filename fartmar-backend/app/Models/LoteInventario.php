<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bodega_id
 * @property int $producto_id
 * @property string $lote
 * @property string $fecha_vencimiento
 * @property int $cantidad
 * @property int $stock_minimo
 * @property-read Bodega $bodega
 * @property-read Producto $producto
 */
class LoteInventario extends Model
{
    use HasFactory;

    protected $table = 'lote_inventarios';

    protected $fillable = [
        'bodega_id',
        'producto_id',
        'lote',
        'fecha_vencimiento',
        'cantidad',
        'stock_minimo',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
    ];

    /**
     * @return BelongsTo<Bodega, $this>
     */
    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
