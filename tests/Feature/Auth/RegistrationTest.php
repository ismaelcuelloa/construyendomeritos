<?php

namespace Tests\Feature\Auth;

use Database\Seeders\CreateRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutar el seeder de roles en los tests
        $this->seed(CreateRolesSeeder::class);
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'phone' => '+1234567890',
        ]);

        // Debe redirigir (302)
        $response->assertStatus(302);

        // Verificar que el usuario fue creado en la BD
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Verificar que fue redirigido al dashboard
        $response->assertRedirect('/admin');
    }
}
