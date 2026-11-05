<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearTrasladoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'bodega_origen_id' => ['required', 'integer'],
            'bodega_destino_id' => ['required', 'integer'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.lote_inventario_id' => ['required', 'integer'],
            'detalles.*.cantidad_solicitada' => ['required', 'integer', 'min:1'],
        ];
    }
}
