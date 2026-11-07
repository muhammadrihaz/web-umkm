<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'type', 'is_public'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }
}
