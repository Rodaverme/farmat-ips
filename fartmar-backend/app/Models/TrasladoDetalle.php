<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrasladoDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'traslado_id',
        'lote_inventario_id',
        'cantidad_solicitada',
        'cantidad_despachada',
        'cantidad_recibida',
    ];

    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteInventario::class, 'lote_inventario_id');
    }
}