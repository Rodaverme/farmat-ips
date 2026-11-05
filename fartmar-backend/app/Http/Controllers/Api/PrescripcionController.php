<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescripcion;
use Illuminate\Http\JsonResponse;

class PrescripcionController extends Controller
{
    /**
     * Consultar una prescripción por ID con sus relaciones (paciente y producto).
     */
    public function show(int $id): JsonResponse
    {
        $prescripcion = Prescripcion::with(['paciente', 'producto', 'medico'])
            ->find($id);

        if (! $prescripcion) {
            return response()->json(['message' => 'Prescripción no encontrada.'], 404);
        }

        return response()->json($prescripcion);
    }
}
