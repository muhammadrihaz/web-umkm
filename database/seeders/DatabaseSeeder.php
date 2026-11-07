<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('DemoSeeder dilewati di luar environment local/testing.');

            return;
        }

        User::unguarded(function (): void {
            User::query()->updateOrCreate(['email' => env('DEMO_ADMIN_EMAIL', 'admin@pangkonan.test')], [
                'name' => 'Admin Demo Pangkonan', 'role' => 'admin', 'phone' => '080000000001',
                'password' => env('DEMO_ADMIN_PASSWORD', 'DemoAdmin123!'),
            ]);
            User::query()->updateOrCreate(['email' => 'customer@pangkonan.test'], [
                'name' => 'Pelanggan Demo', 'role' => 'customer', 'phone' => '080000000002', 'password' => 'DemoCustomer123!',
            ]);
        });
        $category = Category::query()->firstOrCreate(['slug' => 'produk-demo'], ['name' => 'Produk Demo', 'description' => 'Kategori contoh; ganti setelah data pemilik dikonfirmasi.', 'is_active' => true]);
        foreach ([
            ['sku' => 'DEMO-001', 'name' => 'Produk Pangkonan A (Demo)', 'price' => 25000, 'stock_on_hand' => 20],
            ['sku' => 'DEMO-002', 'name' => 'Produk Pangkonan B (Demo)', 'price' => 35000, 'stock_on_hand' => 15],
            ['sku' => 'DEMO-003', 'name' => 'Produk Pangkonan C (Demo)', 'price' => 50000, 'stock_on_hand' => 10],
        ] as $demo) {
            Product::unguarded(fn () => Product::query()->firstOrCreate(['sku' => $demo['sku']], $demo + ['category_id' => $category->id, 'slug' => str($demo['name'])->slug().'-'.strtolower($demo['sku']), 'description' => 'Data produk demonstrasi. Detail bahan, ukuran, dan umur simpan belum dikonfirmasi.', 'unit' => 'item', 'stock_reserved' => 0, 'is_active' => true]));
        }
        DeliveryZone::query()->updateOrCreate(['name' => 'Zona Demo Gianyar'], ['fee' => 10000, 'coverage_notes' => 'Tarif contoh; wajib dikonfirmasi pemilik sebelum penggunaan nyata.', 'is_active' => true]);
    }
}
