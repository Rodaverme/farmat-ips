<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventarioAlertaService;
use Illuminate\Http\JsonResponse;

class InventarioAlertaController extends Controller
{
    public function __construct(private readonly InventarioAlertaService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'alertas_vencimiento' => $this->service->obtenerAlertasVencimiento(),
            'alertas_stock_minimo' => $this->service->obtenerAlertasStockMinimo(),
        ]);
    }
}
