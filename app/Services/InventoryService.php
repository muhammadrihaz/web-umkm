<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function reserve(Order $order, Product $product, int $quantity): StockReservation
    {
        if ($quantity < 1 || ! $product->is_active || $product->available_stock < $quantity) {
            throw ValidationException::withMessages(['cart' => "Stok {$product->name} tidak mencukupi."]);
        }

        $product->increment('stock_reserved', $quantity);
        $reservation = $order->reservations()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'status' => 'active',
            'expires_at' => now()->addMinutes(config('pangkonan.stock_reservation_minutes')),
        ]);
        $this->recordEffect($product, 'order', (string) $order->id, 'reserve', 0, $quantity, 'Reservasi checkout');

        return $reservation;
    }

    public function commit(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $reservations = StockReservation::query()->where('order_id', $order->id)->orderBy('product_id')->lockForUpdate()->get();
            foreach ($reservations as $reservation) {
                if ($reservation->status === 'committed') {
                    continue;
                }
                if ($reservation->status !== 'active') {
                    throw ValidationException::withMessages(['stock' => 'Reservasi tidak lagi aktif. Pesanan memerlukan penanganan admin.']);
                }
                $product = Product::query()->lockForUpdate()->findOrFail($reservation->product_id);
                if ($product->stock_reserved < $reservation->quantity || $product->stock_on_hand < $reservation->quantity) {
                    throw ValidationException::withMessages(['stock' => 'Stok tidak konsisten. Pesanan ditahan untuk pemeriksaan.']);
                }
                $product->stock_on_hand -= $reservation->quantity;
                $product->stock_reserved -= $reservation->quantity;
                $product->save();
                $reservation->update(['status' => 'committed', 'committed_at' => now()]);
                $this->recordEffect($product, 'order', (string) $order->id, 'commit', -$reservation->quantity, -$reservation->quantity, 'Pembayaran/konfirmasi pesanan sah');
            }
        });
    }

    public function release(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason): void {
            $reservations = StockReservation::query()->where('order_id', $order->id)->orderBy('product_id')->lockForUpdate()->get();
            foreach ($reservations as $reservation) {
                if ($reservation->status !== 'active') {
                    continue;
                }
                $product = Product::query()->lockForUpdate()->findOrFail($reservation->product_id);
                $product->decrement('stock_reserved', $reservation->quantity);
                $reservation->update(['status' => 'released', 'released_at' => now()]);
                $this->recordEffect($product, 'order', (string) $order->id, 'release', 0, -$reservation->quantity, $reason);
            }
        });
    }

    public function adjust(Product $product, int $newStock, User $actor, string $reason): Product
    {
        return DB::transaction(function () use ($product, $newStock, $actor, $reason): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($newStock < $locked->stock_reserved) {
                throw ValidationException::withMessages(['stock_on_hand' => 'Stok tidak boleh lebih kecil dari jumlah yang sedang dicadangkan.']);
            }
            $delta = $newStock - $locked->stock_on_hand;
            $locked->update(['stock_on_hand' => $newStock]);
            $this->recordEffect($locked, 'adjustment', (string) now()->getTimestampMs(), 'adjust', $delta, 0, $reason, $actor);

            return $locked->refresh();
        });
    }

    private function recordEffect(Product $product, string $referenceType, string $referenceId, string $type, int $quantityDelta, int $reservedDelta, string $reason, ?User $actor = null): void
    {
        StockMovement::query()->firstOrCreate(
            ['product_id' => $product->id, 'reference_type' => $referenceType, 'reference_id' => $referenceId, 'type' => $type],
            ['actor_id' => $actor?->id, 'quantity_delta' => $quantityDelta, 'reserved_delta' => $reservedDelta, 'reason' => $reason],
        );
    }
}
