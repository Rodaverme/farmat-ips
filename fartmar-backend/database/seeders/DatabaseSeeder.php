<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\LoteInventario;
use App\Models\Paciente;
use App\Models\Prescripcion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios por cada rol solicitado (Sección 3 & 6)[cite: 4, 7]
        $auxiliar = User::create([
            'name' => 'Carlos Auxiliar',
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password'),
            'role' => 'auxiliar_farmacia',
        ]);

        $regente = User::create([
            'name' => 'Dra. Elena Regente',
            'email' => 'regente@farmart.com',
            'password' => Hash::make('password'),
            'role' => 'regente_farmacia',
        ]);

        $medico = User::create([
            'name' => 'Dr. Mario Medico',
            'email' => 'medico@farmart.com',
            'password' => Hash::make('password'),
            'role' => 'medico',
        ]);

        $auditor = User::create([
            'name' => 'Laura Auditora',
            'email' => 'auditor@farmart.com',
            'password' => Hash::make('password'),
            'role' => 'auditor',
        ]);

        $admin = User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@farmart.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Las 3 Bodegas obligatorias (Sección 6)[cite: 7]
        $central = Bodega::create([
            'nombre' => 'Farmacia Central',
            'direccion' => 'Calle 10 # 4-20',
            'telefono' => '6025550101',
            'tipo_bodega' => 'CENTRAL',
        ]);

        $urgencias = Bodega::create([
            'nombre' => 'Farmacia Urgencias',
            'direccion' => 'Calle 10 # 4-25 Edificio B',
            'telefono' => '6025550102',
            'tipo_bodega' => 'URGENCIAS',
        ]);

        $hospitalizacion = Bodega::create([
            'nombre' => 'Bodega Hospitalización',
            'direccion' => 'Piso 3 Torre Principal',
            'telefono' => '6025550103',
            'tipo_bodega' => 'HOSPITALIZACION',
        ]);

        // 3. Los 6 Productos (Uno de control especial - Sección 6)[cite: 7]
        $acetaminofen = Producto::create([
            'nombre' => 'Acetaminofén 500mg Tab',
            'codigo_cum' => 'CUM-1001',
            'es_control_especial' => false,
            'stock' => 470,
        ]);

        $ibuprofeno = Producto::create([
            'nombre' => 'Ibuprofeno 800mg Tab',
            'codigo_cum' => 'CUM-1002',
            'es_control_especial' => false,
            'stock' => 200,
        ]);

        $amoxicilina = Producto::create([
            'nombre' => 'Amoxicilina 500mg Cap',
            'codigo_cum' => 'CUM-1003',
            'es_control_especial' => false,
            'stock' => 150,
        ]);

        $losartan = Producto::create([
            'nombre' => 'Losartán 50mg Tab',
            'codigo_cum' => 'CUM-1004',
            'es_control_especial' => false,
            'stock' => 100,
        ]);

        $tramadol = Producto::create([
            'nombre' => 'Tramadol 50mg Ampolla',
            'codigo_cum' => 'CUM-1005',
            'es_control_especial' => false,
            'stock' => 80,
        ]);

        $morfina = Producto::create([
            'nombre' => 'Morfina 10mg Ampolla',
            'codigo_cum' => 'CUM-2001',
            'es_control_especial' => true, // Control especial (RN-05)[cite: 4, 7]
            'stock' => 40,
        ]);

        // 4. Lotes por Producto (Incluye lote VENCIDO y lote que vence en < 30 días - Sección 6)[cite: 7]
        // Acetaminofén - Lote VENCIDO
        LoteInventario::create([
            'bodega_id' => $central->id,
            'producto_id' => $acetaminofen->id,
            'lote' => 'LOT-ACET-VENCIDO',
            'fecha_vencimiento' => now()->subDays(15)->toDateString(),
            'cantidad' => 50,
            'stock_minimo' => 20,
        ]);

        // Acetaminofén - Lote por vencer (< 30 días)
        LoteInventario::create([
            'bodega_id' => $central->id,
            'producto_id' => $acetaminofen->id,
            'lote' => 'LOT-ACET-PROXIMO',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad' => 120,
            'stock_minimo' => 20,
        ]);

        // Acetaminofén - Lote Vigente
        LoteInventario::create([
            'bodega_id' => $central->id,
            'producto_id' => $acetaminofen->id,
            'lote' => 'LOT-ACET-VIGENTE',
            'fecha_vencimiento' => now()->addMonths(8)->toDateString(),
            'cantidad' => 300,
            'stock_minimo' => 20,
        ]);

        // Morfina en Farmacia Central (Control Especial)
        LoteInventario::create([
            'bodega_id' => $central->id,
            'producto_id' => $morfina->id,
            'lote' => 'LOT-MORF-001',
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad' => 40,
            'stock_minimo' => 10,
        ]);

        // Ibuprofeno en Farmacia Urgencias
        LoteInventario::create([
            'bodega_id' => $urgencias->id,
            'producto_id' => $ibuprofeno->id,
            'lote' => 'LOT-IBU-URG',
            'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
            'cantidad' => 200,
            'stock_minimo' => 15,
        ]);

        // 5. 3 Pacientes Sintéticos (Sección 6)[cite: 7]
        $paciente1 = Paciente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez Gómez',
            'tipo_documento' => 'CC',
            'numero_documento' => '1098765432',
            'fecha_nacimiento' => '1981-05-14',
            'genero' => 'M',
            'telefono' => '3001234567',
            'direccion' => 'Calle 5 # 12-34',
        ]);

        $paciente2 = Paciente::create([
            'nombre' => 'María',
            'apellido' => 'Rodríguez López',
            'tipo_documento' => 'CC',
            'numero_documento' => '1012345678',
            'fecha_nacimiento' => '1994-09-22',
            'genero' => 'F',
            'telefono' => '3109876543',
            'direccion' => 'Carrera 44 # 9C-70',
        ]);

        $paciente3 = Paciente::create([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza Ríos',
            'tipo_documento' => 'CC',
            'numero_documento' => '1055443322',
            'fecha_nacimiento' => '1968-01-30',
            'genero' => 'M',
            'telefono' => '3155558899',
            'direccion' => 'Av Pasoancho # 56-10',
        ]);

        // 6. Prescripciones Vigentes (Una incluye Medicamento de Control Especial - Sección 6)[cite: 7]
        Prescripcion::create([
            'paciente_id' => $paciente1->id,
            'medico_id' => $medico->id,
            'producto_id' => $acetaminofen->id,
            'cantidad_prescrita' => 30,
            'cantidad_dispensada' => 0,
            'fecha_prescripcion' => now()->toDateString(),
            'diagnostico' => 'Dolor muscular agudo',
            'observaciones' => 'Tomar 1 cada 8 horas',
        ]);

        Prescripcion::create([
            'paciente_id' => $paciente2->id,
            'medico_id' => $medico->id,
            'producto_id' => $morfina->id, // Medicamento de Control Especial (RN-05)[cite: 4, 7]
            'cantidad_prescrita' => 5,
            'cantidad_dispensada' => 0,
            'fecha_prescripcion' => now()->toDateString(),
            'diagnostico' => 'Manejo de dolor severo post-operatorio',
            'observaciones' => 'Requiere autorización de regente de farmacia',
        ]);
    }
}
