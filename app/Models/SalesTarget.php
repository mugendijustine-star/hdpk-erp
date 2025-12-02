<?php

namespace App\Models;

use App\Models\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'sales_rep_id',
        'month',
        'year',
        'target_amount_enc',
        'notes',
    ];

    protected function targetAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['target_amount_enc'] ?? null),
            set: fn ($value) => ['target_amount_enc' => $this->storeNumeric($value)],
        );
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }
}
