<?php

namespace App\Services;

use App\Models\LoteInventario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class InventarioAlertaService
{
    /**
     * Obtener alertas de lotes próximos a vencer (<= 90 días) o vencidos.
     *
     * @return Collection<int, LoteInventario>
     */
    public function obtenerAlertasVencimiento(): Collection
    {
        $limiteAlertas = Carbon::today()->addDays(90)->toDateString();

        return LoteInventario::with(['bodega', 'producto'])
            ->where('fecha_vencimiento', '<=', $limiteAlertas)
            ->where('cantidad', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Obtener alertas de stock crítico (cantidad <= stock_minimo).
     *
     * @return Collection<int, LoteInventario>
     */
    public function obtenerAlertasStockMinimo(): Collection
    {
        return LoteInventario::with(['bodega', 'producto'])
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->orderBy('cantidad', 'asc')
            ->get();
    }
}
