<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'sale_id',
        'method',
    ];

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['amount_enc'] ?? null),
            set: fn ($value) => ['amount_enc' => $this->storeNumeric($value)],
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
