<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAudit extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'branch_id',
        'date',
        'cashier_id',
        'expected_cash',
        'counted_cash',
        'difference',
        'reason',
        'status',
        'submitted_by',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected function expectedCash(): Attribute
    {
        return $this->secureNumeric('expected_cash_enc');
    }

    protected function countedCash(): Attribute
    {
        return $this->secureNumeric('counted_cash_enc');
    }

    protected function difference(): Attribute
    {
        return $this->secureNumeric('difference_enc');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
