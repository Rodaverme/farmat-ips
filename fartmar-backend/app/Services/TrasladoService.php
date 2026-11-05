<?php

namespace App\Services;

use App\Models\KardexMovimiento;
use App\Models\LoteInventario;
use App\Models\Producto;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrasladoService
{
    /**
     * Crear solicitud de traslado (Pasa a estado SOLICITADO)
     *
     * @param  array<string, mixed>  $data
     */
    public function crearSolicitud(array $data, User $usuarioSolicitante): Traslado
    {
        if ($data['bodega_origen_id'] === $data['bodega_destino_id']) {
            throw new InvalidArgumentException('La bodega origen y destino no pueden ser iguales.');
        }

        return DB::transaction(function () use ($data, $usuarioSolicitante): Traslado {
            $traslado = Traslado::create([
                'bodega_origen_id' => $data['bodega_origen_id'],
                'bodega_destino_id' => $data['bodega_destino_id'],
                'solicitante_id' => $usuarioSolicitante->id,
                'estado' => 'SOLICITADO',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['detalles'] as $item) {
                /** @var LoteInventario $lote */
                $lote = LoteInventario::findOrFail($item['lote_inventario_id']);

                $hoy = Carbon::today()->toDateString();
                if ($lote->fecha_vencimiento < $hoy) {
                    throw new InvalidArgumentException("El lote {$lote->lote} se encuentra vencido y no se puede trasladar.");
                }

                TrasladoDetalle::create([
                    'traslado_id' => $traslado->id,
                    'lote_inventario_id' => $lote->id,
                    'cantidad_solicitada' => $item['cantidad_solicitada'],
                ]);
            }

            return $traslado->load('detalles.lote');
        });
    }

    /**
     * Aprobar traslado (Pasa a estado APROBADO)
     */
    public function aprobarTraslado(int $trasladoId, User $usuarioAprobador): Traslado
    {
        /** @var Traslado $traslado */
        $traslado = Traslado::findOrFail($trasladoId);

        if ($traslado->estado !== 'SOLICITADO') {
            throw new InvalidArgumentException('Solo se pueden aprobar traslados en estado SOLICITADO.');
        }

        // RN-08: Quien solicita no puede aprobar
        if ($traslado->solicitante_id === $usuarioAprobador->id) {
            throw new InvalidArgumentException('El usuario que solicita un traslado no puede aprobarlo.');
        }

        if ($usuarioAprobador->role !== 'regente_farmacia' && $usuarioAprobador->role !== 'admin') {
            throw new InvalidArgumentException('Solo el Regente de Farmacia o Admin puede aprobar traslados.');
        }

        $traslado->estado = 'APROBADO';
        $traslado->aprobador_id = $usuarioAprobador->id;
        $traslado->save();

        return $traslado;
    }

    /**
     * Despachar traslado (Descuenta stock en bodega origen y registra salida en Kardex)
     */
    public function despacharTraslado(int $trasladoId): Traslado
    {
        return DB::transaction(function () use ($trasladoId): Traslado {
            /** @var Traslado $traslado */
            $traslado = Traslado::with('detalles.lote.producto')->findOrFail($trasladoId);

            if ($traslado->estado !== 'APROBADO') {
                throw new InvalidArgumentException('Solo se pueden despachar traslados en estado APROBADO.');
            }

            /** @var TrasladoDetalle $detalle */
            foreach ($traslado->detalles as $detalle) {
                /** @var LoteInventario $loteOrigen */
                $loteOrigen = $detalle->lote;
                /** @var Producto $producto */
                $producto = $loteOrigen->producto;

                if ($loteOrigen->cantidad < $detalle->cantidad_solicitada) {
                    throw new InvalidArgumentException("Stock insuficiente en origen para el lote {$loteOrigen->lote}.");
                }

                // Descontar stock del lote en bodega origen
                $loteOrigen->cantidad -= $detalle->cantidad_solicitada;
                $loteOrigen->save();

                $detalle->cantidad_despachada = $detalle->cantidad_solicitada;
                $detalle->save();

                // Actualizar stock general del producto
                $saldoAnterior = (int) $producto->stock;
                $saldoNuevo = $saldoAnterior - $detalle->cantidad_solicitada;
                $producto->stock = $saldoNuevo;
                $producto->save();

                // Registrar salida por traslado en Kardex
                KardexMovimiento::create([
                    'bodega_id' => $traslado->bodega_origen_id,
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'SALIDA_TRASLADO',
                    'cantidad' => $detalle->cantidad_solicitada,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'referencia' => "Traslado #{$traslado->id}",
                    'observaciones' => "Salida por traslado a bodega #{$traslado->bodega_destino_id}",
                ]);
            }

            $traslado->estado = 'EN_TRANSITO';
            $traslado->save();

            return $traslado;
        });
    }

    /**
     * Recibir traslado (Incrementa stock en bodega destino y registra entrada en Kardex)
     *
     * @param  array<int, array{detalle_id: int, cantidad_recibida: int}>  $detallesRecibidos
     */
    public function recibirTraslado(int $trasladoId, array $detallesRecibidos): Traslado
    {
        return DB::transaction(function () use ($trasladoId, $detallesRecibidos): Traslado {
            /** @var Traslado $traslado */
            $traslado = Traslado::with('detalles.lote.producto')->findOrFail($trasladoId);

            if ($traslado->estado !== 'EN_TRANSITO') {
                throw new InvalidArgumentException('Solo se pueden recibir traslados en estado EN_TRANSITO.');
            }

            $hayDiscrepancia = false;

            foreach ($detallesRecibidos as $recibido) {
                /** @var TrasladoDetalle $detalle */
                $detalle = TrasladoDetalle::findOrFail($recibido['detalle_id']);
                /** @var LoteInventario $loteOrigen */
                $loteOrigen = $detalle->lote;
                /** @var Producto $producto */
                $producto = $loteOrigen->producto;

                $cantRecibida = (int) $recibido['cantidad_recibida'];
                $detalle->cantidad_recibida = $cantRecibida;
                $detalle->save();

                if ($cantRecibida !== $detalle->cantidad_despachada) {
                    $hayDiscrepancia = true;
                }

                // Incrementar stock del lote en bodega destino
                /** @var LoteInventario $loteDestino */
                $loteDestino = LoteInventario::firstOrCreate(
                    [
                        'bodega_id' => $traslado->bodega_destino_id,
                        'producto_id' => $producto->id,
                        'lote' => $loteOrigen->lote,
                    ],
                    [
                        'fecha_vencimiento' => $loteOrigen->fecha_vencimiento,
                        'cantidad' => 0,
                        'stock_minimo' => $loteOrigen->stock_minimo,
                    ]
                );

                $loteDestino->cantidad += $cantRecibida;
                $loteDestino->save();

                // Actualizar stock general del producto
                $saldoAnterior = (int) $producto->stock;
                $saldoNuevo = $saldoAnterior + $cantRecibida;
                $producto->stock = $saldoNuevo;
                $producto->save();

                // Registrar entrada por traslado en Kardex
                KardexMovimiento::create([
                    'bodega_id' => $traslado->bodega_destino_id,
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'ENTRADA_TRASLADO',
                    'cantidad' => $cantRecibida,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $saldoNuevo,
                    'referencia' => "Traslado #{$traslado->id}",
                    'observaciones' => "Entrada por traslado desde bodega #{$traslado->bodega_origen_id}",
                ]);
            }

            $traslado->estado = $hayDiscrepancia ? 'RECIBIDO_PARCIAL' : 'RECIBIDO';
            $traslado->save();

            return $traslado;
        });
    }
}
