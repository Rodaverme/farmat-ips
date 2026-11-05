<?php

namespace App\Services;

use App\Models\KardexMovimiento;
use App\Models\LoteInventario;
use App\Models\Prescripcion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class DispensacionService
{
    /**
     * Procesar la dispensación de medicamentos en una transacción atómica.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function procesarDispensacion(array $data, User $usuarioActual): array
    {
        return DB::transaction(function () use ($data) {
            /** @var Prescripcion $prescripcion */
            $prescripcion = Prescripcion::with('producto')->findOrFail($data['prescripcion_id']);

            /** @var Producto|null $producto */
            $producto = $prescripcion->producto;
            if (! $producto) {
                throw new InvalidArgumentException('La prescripción médica no posee un producto asignado.');
            }

            $cantidadPendiente = (int) $data['cantidad'];

            // 1. Validar medicamentos de control especial (2da Firma de Regente)
            if ($producto->es_control_especial) {
                if (empty($data['regente_email']) || empty($data['regente_password'])) {
                    throw new InvalidArgumentException('El medicamento requiere autorización de 2da firma del Regente de Farmacia.');
                }

                /** @var User|null $regente */
                $regente = User::where('email', $data['regente_email'])
                    ->where('role', 'regente_farmacia')
                    ->first();

                if (! $regente || ! Hash::check((string) $data['regente_password'], (string) $regente->password)) {
                    throw new InvalidArgumentException('Las credenciales de 2da firma del Regente no son válidas.');
                }
            }

            // 2. Validar saldo de la prescripción
            $maxDispensable = $prescripcion->cantidad_prescrita - $prescripcion->cantidad_dispensada;
            if ($cantidadPendiente > $maxDispensable) {
                throw new InvalidArgumentException("La cantidad supera el saldo de la prescripción. Máximo permitido: {$maxDispensable}.");
            }

            // 3. Consultar lotes vigentes de la bodega elegida ordenados por FEFO
            $hoy = Carbon::today()->toDateString();
            $lotes = LoteInventario::where('bodega_id', $data['bodega_id'])
                ->where('producto_id', $producto->id)
                ->where('cantidad', '>', 0)
                ->where('fecha_vencimiento', '>=', $hoy)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            $stockDisponible = (int) $lotes->sum('cantidad');
            if ($stockDisponible < $cantidadPendiente) {
                throw new InvalidArgumentException("Stock insuficiente en la bodega seleccionada. Disponible: {$stockDisponible}.");
            }

            $lotesDescontados = [];
            $porDescontar = $cantidadPendiente;

            // 4. Descontar stock por FEFO y registrar en KardexMovimiento
            /** @var LoteInventario $lote */
            foreach ($lotes as $lote) {
                if ($porDescontar <= 0) {
                    break;
                }

                $descuento = min($lote->cantidad, $porDescontar);
                $lote->cantidad -= $descuento;
                $lote->save();

                $porDescontar -= $descuento;

                $saldoAnterior = (int) $producto->stock;
                $saldoNuevo = $saldoAnterior - $descuento;

                $producto->stock = $saldoNuevo;
                $producto->save();

                KardexMovimiento::create([
                    'bodega_id' => $data['bodega_id'],
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'DISPENSACION',
                    'cantidad' => $descuento,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'referencia' => "Prescripción #{$prescripcion->id} (Key: {$data['idempotency_key']})",
                    'observaciones' => "Lote consumido: {$lote->lote}",
                ]);

                $lotesDescontados[] = [
                    'lote' => $lote->lote,
                    'cantidad_descontada' => $descuento,
                ];
            }

            // 5. Actualizar la prescripción
            $prescripcion->cantidad_dispensada += $cantidadPendiente;
            $prescripcion->save();

            return [
                'prescripcion_id' => $prescripcion->id,
                'producto' => $producto->nombre,
                'cantidad_dispensada' => $cantidadPendiente,
                'lotes_afectados' => $lotesDescontados,
            ];
        });
    }
}
