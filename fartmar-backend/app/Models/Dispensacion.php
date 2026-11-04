<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'prescripcion_id',
        'bodega_id',
        'user_id',
        'autorizado_por_regente_id', // RN-05: Para medicamentos de control especial
        'cantidad_total',
        'idempotency_key',           // RN-09: Evitar salidas duplicadas
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function prescripcion(): BelongsTo
    {
        return $this->belongsTo(Prescripcion::class);
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function regenteAutorizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por_regente_id');
    }
}
