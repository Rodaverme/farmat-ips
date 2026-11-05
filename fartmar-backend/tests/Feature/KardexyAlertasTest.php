<?php

namespace Tests\Feature;

use App\Models\Bodega;
use App\Models\KardexMovimiento;
use App\Models\LoteInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KardexYAlertasTest extends TestCase
{
    use RefreshDatabase;

    public function test_consulta_de_kardex_con_filtros(): void
    {
        $auditor = User::create([
            'name' => 'Auditor General',
            'email' => 'auditor@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auditor',
        ]);

        $bodega = Bodega::create([
            'nombre' => 'Farmacia Central',
            'direccion' => 'Calle 10 # 4-20',
            'telefono' => '6025550101',
            'tipo_bodega' => 'CENTRAL',
        ]);

        $producto = Producto::create([
            'nombre' => 'Acetaminofen 500mg',
            'codigo_cum' => 'CUM-1001',
            'es_control_especial' => false,
            'stock' => 50,
        ]);

        KardexMovimiento::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'DISPENSACION',
            'cantidad' => 10,
            'saldo_anterior' => 60,
            'saldo_nuevo' => 50,
            'referencia' => 'Prescripcion #1',
        ]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->getJson("/api/kardex?bodega_id={$bodega->id}&producto_id={$producto->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.tipo_movimiento', 'DISPENSACION')
            ->assertJsonPath('data.0.cantidad', 10);
    }

    public function test_alertas_de_vencimiento_y_stock_minimo_rn11(): void
    {
        $auxiliar = User::create([
            'name' => 'Carlos Auxiliar',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $bodega = Bodega::create([
            'nombre' => 'Farmacia Urgencias',
            'direccion' => 'Calle 10 # 4-25',
            'telefono' => '6025550102',
            'tipo_bodega' => 'URGENCIAS',
        ]);

        $producto = Producto::create([
            'nombre' => 'Amoxicilina 500mg',
            'codigo_cum' => 'CUM-1003',
            'es_control_especial' => false,
            'stock' => 5,
        ]);

        // Lote a vencer en 45 días (< 90 días) y stock por debajo del mínimo (2 <= 10)
        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-CRITICO',
            'fecha_vencimiento' => Carbon::now()->addDays(45)->toDateString(),
            'cantidad' => 2,
            'stock_minimo' => 10,
        ]);

        $response = $this->actingAs($auxiliar, 'sanctum')
            ->getJson('/api/inventario/alertas');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'alertas_vencimiento')
            ->assertJsonCount(1, 'alertas_stock_minimo');
    }
}
