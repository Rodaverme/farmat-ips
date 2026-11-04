<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string $codigo
 * @property string|null $ubicacion
 * @property bool $es_principal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Bodega extends Model
{
    use HasFactory;

    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'codigo',
        'ubicacion',
        'es_principal',
    ];

    /**
     * @return HasMany<LoteInventario, $this>
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class);
    }
}