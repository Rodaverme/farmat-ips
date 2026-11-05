<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DispensacionRequest;
use App\Models\User;
use App\Services\DispensacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class DispensacionController extends Controller
{
    public function store(DispensacionRequest $request, DispensacionService $service): JsonResponse
    {
        $idempotencyKey = $request->validated('idempotency_key');
        $cacheKey = "dispensacion_idempotency_{$idempotencyKey}";

        if (Cache::has($cacheKey)) {
            return response()->json([
                'message' => 'Esta transacción ya fue procesada anteriormente.',
                'resultado' => Cache::get($cacheKey),
            ], Response::HTTP_OK);
        }

        try {
            /** @var User $user */
            $user = $request->user();

            $resultado = $service->procesarDispensacion($request->validated(), $user);

            Cache::put($cacheKey, $resultado, now()->addHours(24));

            return response()->json([
                'message' => 'Dispensación registrada exitosamente.',
                'resultado' => $resultado,
            ], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
