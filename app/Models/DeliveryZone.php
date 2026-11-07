<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'fee', 'coverage_notes', 'is_active'])]
class DeliveryZone extends Model
{
    protected function casts(): array
    {
        return ['fee' => 'integer', 'is_active' => 'boolean'];
    }
}
