<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_ensure_admin_command_creates_a_verified_admin_account(): void
    {
        $this->artisan('app:ensure-admin', [
            '--name' => 'Administrator Produksi',
            '--email' => 'admin@example.test',
            '--password' => 'StrongPassword123!',
        ])->assertSuccessful();

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('StrongPassword123!', $admin->password));
    }
}
