<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo_cum',
        'es_control_especial',
        'stock',
    ];
    //

    protected $casts = [
        'es_control_especial' => 'boolean',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class);
    }
}
