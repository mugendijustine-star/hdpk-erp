<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'branch_id',
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
        'expected_cash' => 'float',
        'counted_cash' => 'float',
        'difference' => 'float',
    ];

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
