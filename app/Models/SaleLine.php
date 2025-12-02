<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleLine extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'sale_id',
        'product_variant_id',
    ];

    protected function qty(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['qty_enc'] ?? null),
            set: fn ($value) => ['qty_enc' => $this->storeNumeric($value)],
        );
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['unit_price_enc'] ?? null),
            set: fn ($value) => ['unit_price_enc' => $this->storeNumeric($value)],
        );
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['line_total_enc'] ?? null),
            set: fn ($value) => ['line_total_enc' => $this->storeNumeric($value)],
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
