<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use illuminate\Database\relations\BelongsTo;

class KardexMovimiento extends Model
{

use HasFactory;


public $timestamps = true;
const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'bodega_id',
        'lote_inventario_id',
        'user_id',
        'cantidad',
        'tipo_movimiento',
        'motivo',
        'fecha_movimiento',
        'idempotency_key',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }


    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }
    


//
}
