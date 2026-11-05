<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KardexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function __construct(private readonly KardexService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'producto_id' => ['nullable', 'integer'],
            'bodega_id' => ['nullable', 'integer'],
            'tipo_movimiento' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $movimientos = $this->service->consultarMovimientos($filtros);

        return response()->json($movimientos);
    }
}
