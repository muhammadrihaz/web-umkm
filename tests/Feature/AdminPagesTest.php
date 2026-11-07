<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_operational_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($admin);
        foreach (['admin.categories.index', 'admin.products.index', 'admin.inventory.index', 'admin.orders.index', 'admin.customers.index', 'admin.payments.index', 'admin.delivery-zones.index', 'admin.reports.index', 'admin.settings.edit'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('admin.customers.show', $customer))->assertOk()->assertSee($customer->email);
    }

    public function test_admin_can_manage_category_zone_and_public_business_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Kategori Uji', 'slug' => 'kategori-uji', 'is_active' => 1])
            ->assertRedirect(route('admin.categories.index'));
        $this->post(route('admin.delivery-zones.store'), ['name' => 'Zona Uji', 'fee' => 12000, 'coverage_notes' => 'Area uji', 'is_active' => 1])
            ->assertRedirect();
        $this->put(route('admin.settings.update'), [
            'business_name' => 'Pangkonan Uji',
            'business_whatsapp' => '+628123456789',
            'business_address' => 'Alamat usaha uji',
            'cod_enabled' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['slug' => 'kategori-uji', 'is_active' => 1]);
        $this->assertDatabaseHas('delivery_zones', ['name' => 'Zona Uji', 'fee' => 12000]);
        $this->assertDatabaseHas('settings', ['key' => 'business.name', 'value' => 'Pangkonan Uji']);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Pangkonan Uji');
    }
}
