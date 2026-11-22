<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_stock_from_zero_and_record_the_adjustment(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock_on_hand' => 0, 'stock_reserved' => 0]);

        $this->actingAs($admin)->from('/admin/stok')
            ->put(route('admin.inventory.update', $product), ['stock_on_hand' => 25, 'reason' => 'Penambahan stok baru'])
            ->assertRedirect('/admin/stok')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 25, 'stock_reserved' => 0]);
        $this->assertSame(25, $product->refresh()->available_stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id, 'actor_id' => $admin->id,
            'type' => 'adjust', 'quantity_delta' => 25, 'reserved_delta' => 0,
            'reason' => 'Penambahan stok baru',
        ]);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_adjustment_below_reserved_stock_is_rejected_without_a_movement(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock_on_hand' => 10, 'stock_reserved' => 3]);

        $this->actingAs($admin)->from('/admin/stok')
            ->put(route('admin.inventory.update', $product), ['stock_on_hand' => 2, 'reason' => 'Koreksi hasil hitung'])
            ->assertRedirect('/admin/stok')
            ->assertSessionHasErrors(['stock_on_hand' => 'Stok tidak boleh lebih kecil dari jumlah yang sedang dicadangkan.']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 10, 'stock_reserved' => 3]);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_customer_cannot_adjust_stock(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create(['stock_on_hand' => 0]);

        $this->actingAs($customer)
            ->put(route('admin.inventory.update', $product), ['stock_on_hand' => 25, 'reason' => 'Penambahan stok baru'])
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 0]);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
