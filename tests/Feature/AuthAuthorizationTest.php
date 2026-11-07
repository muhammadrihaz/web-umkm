<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_inject_admin_role(): void
    {
        $response = $this->post('/daftar', [
            'name' => 'Pelanggan', 'email' => 'pelanggan@example.test', 'phone' => '08123456789',
            'password' => 'Password123', 'password_confirmation' => 'Password123', 'role' => 'admin',
        ]);

        $response->assertRedirect('/katalog');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'pelanggan@example.test', 'role' => 'customer']);
    }

    public function test_customer_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
    }
}
