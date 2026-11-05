<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CrearTrasladoRequest;
use App\Models\User;
use App\Services\TrasladoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class TrasladoController extends Controller
{
    public function __construct(private readonly TrasladoService $service) {}

    public function store(CrearTrasladoRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $traslado = $this->service->crearSolicitud($request->validated(), $user);

            return response()->json([
                'message' => 'Traslado solicitado exitosamente.',
                'traslado' => $traslado,
            ], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function aprobar(int $id, Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $traslado = $this->service->aprobarTraslado($id, $user);

            return response()->json([
                'message' => 'Traslado aprobado exitosamente.',
                'traslado' => $traslado,
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function despachar(int $id): JsonResponse
    {
        try {
            $traslado = $this->service->despacharTraslado($id);

            return response()->json([
                'message' => 'Traslado despachado y en tránsito.',
                'traslado' => $traslado,
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function recibir(int $id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.detalle_id' => ['required', 'integer'],
            'detalles.*.cantidad_recibida' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $traslado = $this->service->recibirTraslado($id, $data['detalles']);

            return response()->json([
                'message' => 'Traslado recibido exitosamente.',
                'traslado' => $traslado,
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function index(): JsonResponse
    {
        $traslados = \App\Models\Traslado::with([
            'detalles', 
            'bodegaOrigen', 
            'bodegaDestino', 
            'solicitante', 
            'aprobador'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($traslados, Response::HTTP_OK);
    }
}
