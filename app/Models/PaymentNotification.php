<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_id', 'event_key', 'provider_order_id', 'provider_transaction_id', 'transaction_status', 'signature', 'payload', 'received_at', 'processed_at', 'attempts', 'last_error'])]
class PaymentNotification extends Model
{
    protected function casts(): array
    {
        return ['payload' => 'array', 'received_at' => 'datetime', 'processed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
