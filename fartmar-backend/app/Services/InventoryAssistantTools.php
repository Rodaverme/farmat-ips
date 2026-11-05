<?php

namespace App\Services;

use App\Models\LoteInventario;
use Carbon\Carbon;

class InventoryAssistantTools
{
    public static function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_lotes_vencimiento',
                    'description' => 'Consulta lotes de medicamentos que vencen en un rango de días específico en una bodega.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'producto' => ['type' => 'string', 'description' => 'Nombre o código del medicamento'],
                            'dias' => ['type' => 'integer', 'description' => 'Número de días límite para el vencimiento'],
                            'bodega' => ['type' => 'string', 'description' => 'Nombre de la bodega (ej. Farmacia Central)'],
                        ],
                        'required' => ['dias'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_stock_minimo',
                    'description' => 'Lista todos los productos que se encuentran por debajo del stock mínimo definido por bodega.',
                    'parameters' => ['type' => 'object', 'properties' => []],
                ],
            ],
        ];
    }

    public static function executeTool(string $toolName, array $arguments): mixed
    {
        if ($toolName === 'consultar_lotes_vencimiento') {
            $dias = $arguments['dias'] ?? 90;
            $limite = Carbon::today()->addDays($dias);

            $query = LoteInventario::with(['producto', 'bodega'])
                ->where('cantidad', '>', 0)
                ->whereDate('fecha_vencimiento', '<=', $limite);

            if (! empty($arguments['producto'])) {
                $query->whereHas('producto', fn ($q) => $q->where('nombre', 'ilike', "%{$arguments['producto']}%"));
            }

            if (! empty($arguments['bodega'])) {
                $query->whereHas('bodega', fn ($q) => $q->where('nombre', 'ilike', "%{$arguments['bodega']}%"));
            }

            return $query->get();
        }

        if ($toolName === 'consultar_stock_minimo') {
            return LoteInventario::with(['producto', 'bodega'])
                ->whereColumn('cantidad', '<=', 'stock_minimo')
                ->get();
        }

        return null;
    }
}
