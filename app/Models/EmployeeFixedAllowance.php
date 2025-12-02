<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFixedAllowance extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'employee_id',
        'type',
        'amount_enc',
    ];

    public function getAmountAttribute(): ?float
    {
        return $this->decodeNumeric($this->amount_enc ?? null);
    }

    public function setAmountAttribute(?float $value): void
    {
        $this->attributes['amount_enc'] = $this->encodeNumeric($value);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
