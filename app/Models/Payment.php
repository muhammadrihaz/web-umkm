<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['attempt_number', 'provider', 'provider_order_id', 'provider_transaction_id', 'status', 'amount', 'currency', 'snap_token', 'redirect_url', 'provider_payload', 'expires_at', 'paid_at'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return ['status' => PaymentStatus::class, 'amount' => 'integer', 'provider_payload' => 'array', 'expires_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PaymentAdjustment::class);
    }
}
