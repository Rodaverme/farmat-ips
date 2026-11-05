<?php

namespace Tests\Feature;

use App\Models\Bodega;
use App\Models\LoteInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventario_returns_lotes_ordered_by_fefo(): void
    {
        $user = User::create([
            'name' => 'Auxiliar Test',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $bodega = Bodega::create([
            'nombre' => 'Farmacia Central',
            'direccion' => 'Calle 10 # 4-20',
            'telefono' => '6025550101',
            'tipo_bodega' => 'CENTRAL',
        ]);

        $producto = Producto::create([
            'nombre' => 'Acetaminofen 500mg Tab',
            'codigo_cum' => 'CUM-1001',
            'es_control_especial' => false,
            'stock' => 70,
        ]);

        // Lote 2: Vence más tarde (6 meses)
        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-ACET-VIGENTE',
            'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
            'cantidad' => 50,
            'stock_minimo' => 10,
        ]);

        // Lote 1: Vence más pronto (10 días) -> Debe ir de primero por FEFO
        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-ACET-PROXIMO',
            'fecha_vencimiento' => Carbon::now()->addDays(10)->toDateString(),
            'cantidad' => 20,
            'stock_minimo' => 10,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/inventario');

        $response->assertStatus(200);

        /** @var array<int, array<string, mixed>> $lotes */
        $lotes = $response->json('data.0.lotes');

        $this->assertEquals('LOT-ACET-PROXIMO', $lotes[0]['lote']);
        $this->assertEquals('LOT-ACET-VIGENTE', $lotes[1]['lote']);
    }

    public function test_alertas_vencimiento_detects_expired_and_near_expiration_lotes(): void
    {
        $user = User::create([
            'name' => 'Regente Test',
            'email' => 'regente@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'regente_farmacia',
        ]);

        $bodega = Bodega::create([
            'nombre' => 'Farmacia Urgencias',
            'direccion' => 'Calle 10 # 4-25',
            'telefono' => '6025550102',
            'tipo_bodega' => 'URGENCIAS',
        ]);

        $producto = Producto::create([
            'nombre' => 'Ibuprofeno 800mg Tab',
            'codigo_cum' => 'CUM-1002',
            'es_control_especial' => false,
            'stock' => 20,
        ]);

        // Lote Vencido (hace 5 días)
        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-IBU-VENCIDO',
            'fecha_vencimiento' => Carbon::now()->subDays(5)->toDateString(),
            'cantidad' => 10,
            'stock_minimo' => 5,
        ]);

        // Lote Por Vencer (en 15 días)
        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-IBU-PROXIMO',
            'fecha_vencimiento' => Carbon::now()->addDays(15)->toDateString(),
            'cantidad' => 10,
            'stock_minimo' => 5,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/inventario/vencimientos');

        $response->assertStatus(200)
            ->assertJsonPath('alertas.vencidos_count', 1)
            ->assertJsonPath('alertas.por_vencer_count', 1);
    }
}
