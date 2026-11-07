<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'actor_id', 'reference_type', 'reference_id', 'type', 'quantity_delta', 'reserved_delta', 'reason'])]
class StockMovement extends Model {}
