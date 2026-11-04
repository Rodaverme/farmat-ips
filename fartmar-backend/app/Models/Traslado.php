<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $bodega_origen_id
 * @property int $bodega_destino_id
 * @property int $solicitante_id
 * @property int|null $aprobador_id
 * @property string $estado
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Traslado extends Model
{
    use HasFactory;

    protected $table = 'traslados';

    protected $fillable = [
        'bodega_origen_id',
        'bodega_destino_id',
        'solicitante_id',
        'aprobador_id',
        'estado',
        'observaciones',
    ];

    /**
     * @return BelongsTo<Bodega, $this>
     */
    public function bodegaOrigen(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_origen_id');
    }

    /**
     * @return BelongsTo<Bodega, $this>
     */
    public function bodegaDestino(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    /**
     * @return HasMany<TrasladoDetalle, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class);
    }
}