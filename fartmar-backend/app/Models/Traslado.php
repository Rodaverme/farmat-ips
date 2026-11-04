<?php

namespace App\Models;

use illuminate\Database\Eloquent\Factories\HasFactory;
use illuminate\Database\Eloquent\Model;
use illuminate\Database\relations\BelongsTo;
use illuminate\Database\relations\HasMany;

class Traslado extends Model
{
    
    use HasFactory;

    public const ESTADO_BORRADOR = 'BORRADOR';
    public const ESTADO_SOLICITADO = 'SOLICITADO';
    public const ESTADO_APROBADO = 'APROBADO';
    public const ESTADO_EN_TRANSITO = 'EN_TRANSITO';
    public const ESTADO_RECIBIDO = 'RECIBIDO';
    public const ESTADO_RECIBIDO_PARCIAL = 'RECIBIDO_PARCIAL';
    public const ESTADO_ANULADO = 'ANULADO';





    protected $fillable = [
        'bodega_origen_id',
        'bodega_destino_id',
        'solicitante_id',
        'autorizante_id',
        'despachante_id',
        'recibiente_id',
        'fecha_traslado',
        'estado',
        'observaciones',
    ];

    // Relaciones Eloquent[cite: 5]
    public function bodegaOrigen(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_origen_id');
    }

    public function bodegaDestino(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

   public function solicitante(): BelongsTo
{
    return $this->belongsTo(User::class, 'solicitante_id');
}

public function aprobador(): BelongsTo
{
    return $this->belongsTo(User::class, 'autorizante_id');
}

    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class);
    }

    // Helpers para la máquina de estados[cite: 4]
    public function esBorrador(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    public function puedeAprobarse(): bool
    {
        return $this->estado === self::ESTADO_SOLICITADO;
    }

    public function puedeDespacharse(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function puedeRecibirse(): bool
    {
        return $this->estado === self::ESTADO_EN_TRANSITO;
    }

    
//
}
