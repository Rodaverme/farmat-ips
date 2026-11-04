<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\relations\HasMany;

class Bodega extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'tipo_bodega',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class);
    }
    //
}
