<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'sku', 'name', 'slug', 'description', 'unit', 'price', 'is_active'])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock_on_hand' => 'integer', 'stock_reserved' => 'integer', 'is_active' => 'boolean'];
    }

    protected function availableStock(): Attribute
    {
        return Attribute::get(fn (): int => $this->stock_on_hand - $this->stock_reserved);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
