<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'quantity', 'status', 'expires_at', 'committed_at', 'released_at'])]
class StockReservation extends Model
{
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'committed_at' => 'datetime', 'released_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
