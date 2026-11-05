<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispensacionRequest extends FormRequest
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
            'idempotency_key' => ['required', 'string', 'uuid'],
            'prescripcion_id' => ['required', 'integer'],
            'bodega_id' => ['required', 'integer'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'regente_email' => ['nullable', 'email'],
            'regente_password' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'idempotency_key.required' => 'La clave de idempotencia es obligatoria.',
            'idempotency_key.uuid' => 'La clave de idempotencia debe ser un UUID válido.',
            'prescripcion_id.required' => 'La prescripción médica es obligatoria.',
            'bodega_id.required' => 'La bodega de despacho es obligatoria.',
            'cantidad.required' => 'La cantidad a dispensar es obligatoria.',
            'cantidad.min' => 'La cantidad mínima a dispensar debe ser al menos 1.',
        ];
    }
}
