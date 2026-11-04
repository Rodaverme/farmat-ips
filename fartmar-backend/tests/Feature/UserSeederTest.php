<?php


namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederTest extends TestCase

{
    use RefreshDatabase;

    public function test_database_has_seeded_roles(): void
    {
        // Ejecutar el seeder
        $this->seed();

        // Verificar que los usuarios se hayan creado en la base de dato

        // Verificar que los usuarios tengan los datos correctos
        $this->assertDatabaseHas('users', ['email' => 'auxiliar@farmart.com', 'role' => 'auxiliar_farmacia']);
        $this->assertDatabaseHas('users', ['email' => 'regente@farmart.com', 'role' => 'regente_farmacia']);
        $this->assertDatabaseHas('users', ['email' => 'medico@farmart.com', 'role' => 'medico']);
        $this->assertDatabaseHas('users', ['email' => 'auditor@farmart.com', 'role' => 'auditor']);
        $this->assertDatabaseHas('users', ['email' => 'admin@farmart.com', 'role' => 'admin']);;
    }
}