<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'idempotency_key', 'payload_fingerprint', 'order_status', 'payment_status', 'fulfillment_status', 'payment_method', 'fulfillment_method', 'subtotal', 'delivery_fee', 'total', 'recipient_name', 'recipient_phone', 'delivery_address', 'customer_notes', 'exception_reason', 'paid_at', 'cod_received_at', 'completed_at', 'cancelled_at'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'order_status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'subtotal' => 'integer', 'delivery_fee' => 'integer', 'total' => 'integer',
            'paid_at' => 'datetime', 'cod_received_at' => 'datetime', 'completed_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
