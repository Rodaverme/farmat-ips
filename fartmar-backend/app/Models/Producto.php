<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string $codigo_cum
 * @property bool $es_control_especial
 * @property int $stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'codigo_cum',
        'es_control_especial',
        'stock',
    ];

    /**
     * @return HasMany<LoteInventario, $this>
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class);
    }
}