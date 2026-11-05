<?php

namespace Tests\Feature;

use App\Models\Bodega;
use App\Models\KardexMovimiento;
use App\Models\LoteInventario;
use App\Models\Producto;
use App\Models\Traslado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrasladoTest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_completo_de_traslado_con_segregacion_de_funciones(): void
    {
        $auxiliar = User::create([
            'name' => 'Carlos Auxiliar',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $regente = User::create([
            'name' => 'Elena Regente',
            'email' => 'regente@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'regente_farmacia',
        ]);

        $central = Bodega::create([
            'nombre' => 'Farmacia Central',
            'direccion' => 'Calle 10 # 4-20',
            'telefono' => '6025550101',
            'tipo_bodega' => 'CENTRAL',
        ]);

        $urgencias = Bodega::create([
            'nombre' => 'Farmacia Urgencias',
            'direccion' => 'Calle 10 # 4-25',
            'telefono' => '6025550102',
            'tipo_bodega' => 'URGENCIAS',
        ]);

        $producto = Producto::create([
            'nombre' => 'Ibuprofeno 800mg Tab',
            'codigo_cum' => 'CUM-1002',
            'es_control_especial' => false,
            'stock' => 100,
        ]);

        $loteCentral = LoteInventario::create([
            'bodega_id' => $central->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-IBU-01',
            'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
            'cantidad' => 50,
            'stock_minimo' => 10,
        ]);

        // 1. Crear solicitud
        $responseCreate = $this->actingAs($auxiliar, 'sanctum')
            ->postJson('/api/traslados', [
                'bodega_origen_id' => $central->id,
                'bodega_destino_id' => $urgencias->id,
                'observaciones' => 'Abastecimiento urgente',
                'detalles' => [
                    [
                        'lote_inventario_id' => $loteCentral->id,
                        'cantidad_solicitada' => 20,
                    ],
                ],
            ]);

        $responseCreate->assertStatus(201);
        $trasladoId = (int) $responseCreate->json('traslado.id');

        // 2. Probar Segregación de Funciones (RN-08): Quien solicita NO aprueba
        $responseSelfApprove = $this->actingAs($auxiliar, 'sanctum')
            ->postJson("/api/traslados/{$trasladoId}/aprobar");
        $responseSelfApprove->assertStatus(422);

        // 3. Aprobación por parte del Regente
        $responseApprove = $this->actingAs($regente, 'sanctum')
            ->postJson("/api/traslados/{$trasladoId}/aprobar");
        $responseApprove->assertStatus(200);

        // 4. Despacho (Descuento de origen y salida Kardex)
        $responseDespacho = $this->actingAs($auxiliar, 'sanctum')
            ->postJson("/api/traslados/{$trasladoId}/despachar");
        $responseDespacho->assertStatus(200);

        $this->assertDatabaseHas(LoteInventario::class, [
            'id' => $loteCentral->id,
            'cantidad' => 30,
        ]);

        $this->assertDatabaseHas(KardexMovimiento::class, [
            'bodega_id' => $central->id,
            'tipo_movimiento' => 'SALIDA_TRASLADO',
            'cantidad' => 20,
        ]);

        // 5. Recepción en destino (Entrada Kardex y stock en destino)
        /** @var Traslado $traslado */
        $traslado = Traslado::with('detalles')->find($trasladoId);
        $detalleId = $traslado->detalles->first()->id;

        $responseRecibir = $this->actingAs($auxiliar, 'sanctum')
            ->postJson("/api/traslados/{$trasladoId}/recibir", [
                'detalles' => [
                    [
                        'detalle_id' => $detalleId,
                        'cantidad_recibida' => 20,
                    ],
                ],
            ]);

        $responseRecibir->assertStatus(200);

        $this->assertDatabaseHas(LoteInventario::class, [
            'bodega_id' => $urgencias->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-IBU-01',
            'cantidad' => 20,
        ]);

        $this->assertDatabaseHas(KardexMovimiento::class, [
            'bodega_id' => $urgencias->id,
            'tipo_movimiento' => 'ENTRADA_TRASLADO',
            'cantidad' => 20,
        ]);
    }
}
