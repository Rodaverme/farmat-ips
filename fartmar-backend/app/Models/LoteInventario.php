<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use illuminate\Database\relations\BelongsTo;
use illuminate\Database\eloquent\builder;

class LoteInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'bodega_id',
        'lote',
        'fecha_vencimiento',
        'cantidad',
        'stock_minimo',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
    ];
    //

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    
    }




// Scope para filtrar lotes disponibles

    public function scopeDisponible(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('cantidad', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc');

    }

}
