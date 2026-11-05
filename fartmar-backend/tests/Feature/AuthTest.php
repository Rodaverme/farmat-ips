<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'auxiliar@farmart.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'auxiliar@farmart.com',
            'password' => Hash::make('password123'),
            'role' => 'auxiliar_farmacia',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'auxiliar@farmart.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }
}
