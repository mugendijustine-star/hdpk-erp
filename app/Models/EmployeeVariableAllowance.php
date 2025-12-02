<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeVariableAllowance extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'employee_id',
        'type',
        'amount_enc',
        'month',
        'year',
        'status',
        'entered_by',
        'approved_by',
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

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
