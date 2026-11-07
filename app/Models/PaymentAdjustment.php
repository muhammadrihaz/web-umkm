<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_id', 'provider_adjustment_id', 'type', 'amount', 'occurred_at', 'provider_payload'])]
class PaymentAdjustment extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'integer', 'occurred_at' => 'datetime', 'provider_payload' => 'array'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
