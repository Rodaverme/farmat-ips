<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoteInventario;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class InventarioController extends Controller
{
    /**
     * Consultar productos con sus lotes ordenados estrictamente por FEFO.
     */
    public function index(Request $request): JsonResponse
    {
        $bodegaId = $request->query('bodega_id');

        $query = Producto::with(['lotes' => function ($query) use ($bodegaId) {
            if ($bodegaId) {
                $query->where('bodega_id', $bodegaId);
            }
            // Ordenamiento FEFO estricto: Primero los próximos a vencer
            $query->where('cantidad', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc');
        }]);

        if ($request->query('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                    ->orWhere('codigo_cum', 'ilike', "%{$search}%");
            });
        }

        $productos = $query->paginate(15);

        return response()->json($productos, Response::HTTP_OK);
    }

    /**
     * Alertas de vencimiento (Semaforización).
     */
    public function alertasVencimiento(): JsonResponse
    {
        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        // Lotes Vencidos (Rojo)
        $vencidos = LoteInventario::with(['producto', 'bodega'])
            ->where('cantidad', '>', 0)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        // Lotes Por Vencer en <= 30 días (Amarillo)
        $porVencer = LoteInventario::with(['producto', 'bodega'])
            ->where('cantidad', '>', 0)
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $limiteProximo)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return response()->json([
            'alertas' => [
                'vencidos_count' => $vencidos->count(),
                'por_vencer_count' => $porVencer->count(),
                'vencidos' => $vencidos,
                'por_vencer' => $porVencer,
            ],
        ], Response::HTTP_OK);
    }
}
