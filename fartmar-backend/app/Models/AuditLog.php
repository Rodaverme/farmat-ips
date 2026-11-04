<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    // Inmutable: solo se registra la creación
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'accion',        // ej. 'CONSULTA_PACIENTE', 'DISPENSACION_AUTORIZADA'
        'recurso_tipo',  // ej. 'Paciente'
        'recurso_id',
        'ip_address',
        'detalles',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}