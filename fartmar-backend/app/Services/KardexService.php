<?php

namespace App\Services;

use App\Models\KardexMovimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class KardexService
{
    /**
     * Consultar movimientos inmutables del Kardex con filtros.
     *
     * @param  array<string, mixed>  $filtros
     * @return LengthAwarePaginator<int, KardexMovimiento>
     */
    public function consultarMovimientos(array $filtros): LengthAwarePaginator
    {
        $query = KardexMovimiento::with(['bodega', 'producto'])
            ->orderBy('created_at', 'desc');

        if (! empty($filtros['producto_id'])) {
            $query->where('producto_id', $filtros['producto_id']);
        }

        if (! empty($filtros['bodega_id'])) {
            $query->where('bodega_id', $filtros['bodega_id']);
        }

        if (! empty($filtros['tipo_movimiento'])) {
            $query->where('tipo_movimiento', $filtros['tipo_movimiento']);
        }

        if (! empty($filtros['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio']);
        }

        if (! empty($filtros['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin']);
        }

        /** @var LengthAwarePaginator<int, KardexMovimiento> $paginator */
        $paginator = $query->paginate(15);

        return $paginator;
    }
}
