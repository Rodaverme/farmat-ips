<?php

namespace Tests\Feature;

use App\Models\Bodega;
use App\Models\KardexMovimiento;
use App\Models\LoteInventario;
use App\Models\Paciente;
use App\Models\Prescripcion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DispensacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_auxiliar_can_dispense_normal_medication_by_fefo(): void
    {
        $auxiliar = User::create([
            'name' => 'Carlos Auxiliar',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $medico = User::create([
            'name' => 'Dr. Mario Medico',
            'email' => 'medico@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'medico',
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
            'stock' => 100,
        ]);

        $loteFEFO = LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'lote' => 'LOT-PROXIMO',
            'fecha_vencimiento' => Carbon::now()->addDays(10)->toDateString(),
            'cantidad' => 20,
            'stock_minimo' => 5,
        ]);

        $paciente = Paciente::create([
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'tipo_documento' => 'CC',
            'numero_documento' => '1098765432',
            'fecha_nacimiento' => '1981-05-14',
            'genero' => 'M',
            'telefono' => '3001234567',
            'direccion' => 'Calle 5 # 12-34',
        ]);

        $prescripcion = Prescripcion::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'producto_id' => $producto->id,
            'cantidad_prescrita' => 10,
            'cantidad_dispensada' => 0,
            'fecha_prescripcion' => Carbon::now()->toDateString(),
            'diagnostico' => 'Fiebre',
            'observaciones' => 'Tomar cada 8 horas',
        ]);

        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($auxiliar, 'sanctum')
            ->postJson('/api/dispensaciones', [
                'idempotency_key' => $uuid,
                'prescripcion_id' => $prescripcion->id,
                'bodega_id' => $bodega->id,
                'cantidad' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('resultado.cantidad_dispensada', 10);

        // Usamos la clase del modelo para que Eloquent resuelva el nombre exacto de la tabla
        $this->assertDatabaseHas(LoteInventario::class, [
            'id' => $loteFEFO->id,
            'cantidad' => 10,
        ]);

        $this->assertDatabaseHas(KardexMovimiento::class, [
            'bodega_id' => $bodega->id,
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'DISPENSACION',
            'cantidad' => 10,
            'saldo_anterior' => 100,
            'saldo_nuevo' => 90,
        ]);
    }

    public function test_control_especial_requires_valid_second_signature_from_regente(): void
    {
        $auxiliar = User::create([
            'name' => 'Carlos Auxiliar',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $medico = User::create([
            'name' => 'Dr. Mario Medico',
            'email' => 'medico@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'medico',
        ]);

        User::create([
            'name' => 'Elena Regente',
            'email' => 'regente@farmart.com',
            'password' => Hash::make('regente123'),
            'role' => 'regente_farmacia',
        ]);

        $bodega = Bodega::create([
            'nombre' => 'Farmacia Central',
            'direccion' => 'Calle 10 # 4-20',
            'telefono' => '6025550101',
            'tipo_bodega' => 'CENTRAL',
        ]);

        $morfina = Producto::create([
            'nombre' => 'Morfina 10mg Ampolla',
            'codigo_cum' => 'CUM-2001',
            'es_control_especial' => true,
            'stock' => 50,
        ]);

        LoteInventario::create([
            'bodega_id' => $bodega->id,
            'producto_id' => $morfina->id,
            'lote' => 'LOT-MORF-01',
            'fecha_vencimiento' => Carbon::now()->addMonths(6)->toDateString(),
            'cantidad' => 50,
            'stock_minimo' => 5,
        ]);

        $paciente = Paciente::create([
            'nombre' => 'Maria',
            'apellido' => 'Lopez',
            'tipo_documento' => 'CC',
            'numero_documento' => '1012345678',
            'fecha_nacimiento' => '1994-09-22',
            'genero' => 'F',
            'telefono' => '3109876543',
            'direccion' => 'Carrera 44 # 9C-70',
        ]);

        $prescripcion = Prescripcion::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'producto_id' => $morfina->id,
            'cantidad_prescrita' => 2,
            'cantidad_dispensada' => 0,
            'fecha_prescripcion' => Carbon::now()->toDateString(),
            'diagnostico' => 'Dolor severo',
            'observaciones' => 'Uso restringido',
        ]);

        $response1 = $this->actingAs($auxiliar, 'sanctum')
            ->postJson('/api/dispensaciones', [
                'idempotency_key' => Str::uuid()->toString(),
                'prescripcion_id' => $prescripcion->id,
                'bodega_id' => $bodega->id,
                'cantidad' => 2,
            ]);

        $response1->assertStatus(422);

        $response2 = $this->actingAs($auxiliar, 'sanctum')
            ->postJson('/api/dispensaciones', [
                'idempotency_key' => Str::uuid()->toString(),
                'prescripcion_id' => $prescripcion->id,
                'bodega_id' => $bodega->id,
                'cantidad' => 2,
                'regente_email' => 'regente@farmart.com',
                'regente_password' => 'regente123',
            ]);

        $response2->assertStatus(201);
    }
}
