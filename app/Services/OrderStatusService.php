<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function confirmCod(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_method !== 'cod' || $locked->order_status !== OrderStatus::AwaitingConfirmation) {
                throw ValidationException::withMessages(['order' => 'Pesanan tidak dapat dikonfirmasi sebagai COD.']);
            }
            $this->inventoryService->commit($locked);

            return $this->changeOrder($locked, OrderStatus::Confirmed, $actor, 'COD dikonfirmasi admin');
        }, 3);
    }

    public function receiveCod(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_method !== 'cod') {
                throw ValidationException::withMessages(['order' => 'Pesanan bukan COD.']);
            }
            if ($locked->cod_received_at) {
                return $locked;
            }
            if (! in_array($locked->order_status, [OrderStatus::Confirmed, OrderStatus::Processing], true)) {
                throw ValidationException::withMessages(['order' => 'Penerimaan COD hanya dapat dicatat setelah pesanan dikonfirmasi.']);
            }
            $locked->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => now(), 'cod_received_at' => now()]);
            $locked->histories()->create(['actor_id' => $actor->id, 'source' => 'admin', 'state_type' => 'payment', 'from_status' => PaymentStatus::Unpaid->value, 'to_status' => PaymentStatus::Paid->value, 'reason' => 'Uang COD diterima']);

            return $locked->refresh();
        });
    }

    public function updateFulfillment(Order $order, FulfillmentStatus $next, User $actor): Order
    {
        if (! in_array($order->order_status, [OrderStatus::Confirmed, OrderStatus::Processing], true)) {
            throw ValidationException::withMessages(['order' => 'Status pesanan tidak mengizinkan pemenuhan.']);
        }
        if ($order->order_status === OrderStatus::AwaitingPayment || ($order->payment_method === 'midtrans' && $order->payment_status !== PaymentStatus::Paid)) {
            throw ValidationException::withMessages(['order' => 'Pesanan online yang belum dibayar tidak dapat diproses.']);
        }
        if ($order->fulfillment_method === 'pickup' && $next === FulfillmentStatus::OutForDelivery) {
            throw ValidationException::withMessages(['fulfillment_status' => 'Pickup tidak memakai status dalam pengiriman.']);
        }
        if ($order->fulfillment_method === 'delivery' && $next === FulfillmentStatus::ReadyForPickup) {
            throw ValidationException::withMessages(['fulfillment_status' => 'Delivery tidak memakai status siap diambil.']);
        }
        if (in_array($next, [FulfillmentStatus::PickedUp, FulfillmentStatus::Delivered], true) && $order->payment_method === 'cod' && ! $order->cod_received_at) {
            throw ValidationException::withMessages(['payment' => 'Uang COD harus dicatat sebelum pesanan diselesaikan.']);
        }
        $allowed = $order->fulfillment_method === 'pickup'
            ? [
                FulfillmentStatus::Unfulfilled->value => FulfillmentStatus::Preparing,
                FulfillmentStatus::Preparing->value => FulfillmentStatus::ReadyForPickup,
                FulfillmentStatus::ReadyForPickup->value => FulfillmentStatus::PickedUp,
            ]
            : [
                FulfillmentStatus::Unfulfilled->value => FulfillmentStatus::Preparing,
                FulfillmentStatus::Preparing->value => FulfillmentStatus::OutForDelivery,
                FulfillmentStatus::OutForDelivery->value => FulfillmentStatus::Delivered,
            ];
        if (($allowed[$order->fulfillment_status->value] ?? null) !== $next) {
            throw ValidationException::withMessages(['fulfillment_status' => 'Transisi status pemenuhan tidak valid.']);
        }
        $previous = $order->fulfillment_status->value;
        $order->update(['fulfillment_status' => $next]);
        $order->histories()->create(['actor_id' => $actor->id, 'source' => 'admin', 'state_type' => 'fulfillment', 'from_status' => $previous, 'to_status' => $next->value]);
        if (in_array($next, [FulfillmentStatus::PickedUp, FulfillmentStatus::Delivered], true)) {
            $this->changeOrder($order, OrderStatus::Completed, $actor, 'Pesanan diterima pelanggan');
            $order->update(['completed_at' => now()]);
        } elseif ($order->order_status === OrderStatus::Confirmed) {
            $this->changeOrder($order, OrderStatus::Processing, $actor, 'Pemenuhan dimulai');
        }

        return $order->refresh();
    }

    public function cancelUnpaid(Order $order, User $actor, string $reason): Order
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            throw ValidationException::withMessages(['order' => 'Pesanan berbayar memerlukan penanganan admin/refund.']);
        }
        if (! in_array($order->order_status, [OrderStatus::AwaitingPayment, OrderStatus::AwaitingConfirmation], true)
            || $order->fulfillment_status !== FulfillmentStatus::Unfulfilled) {
            throw ValidationException::withMessages(['order' => 'Pesanan yang sudah diproses tidak dapat dibatalkan dari alur ini.']);
        }
        $this->inventoryService->release($order, $reason);
        $order->update(['cancelled_at' => now()]);

        return $this->changeOrder($order, OrderStatus::Cancelled, $actor, $reason);
    }

    private function changeOrder(Order $order, OrderStatus $next, User $actor, string $reason): Order
    {
        $previous = $order->order_status->value;
        $order->update(['order_status' => $next]);
        $order->histories()->create(['actor_id' => $actor->id, 'source' => $actor->isAdmin() ? 'admin' : 'customer', 'state_type' => 'order', 'from_status' => $previous, 'to_status' => $next->value, 'reason' => $reason]);

        return $order->refresh();
    }
}
