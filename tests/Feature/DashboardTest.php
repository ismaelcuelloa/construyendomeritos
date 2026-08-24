<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        // Cambiado de /dashboard a /mis_cursos
        $response = $this->get('/mis_cursos');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Cambiado de /dashboard a /mis_cursos
        $response = $this->get('/mis_cursos');

        $response->assertStatus(200);
    }
}
