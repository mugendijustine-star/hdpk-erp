<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_salary_enc',
        'fixed_allowances_enc',
        'variable_allowances_enc',
        'overtime_enc',
        'deductions_enc',
        'net_pay_enc',
    ];

    public function getBasicSalaryAttribute(): ?float
    {
        return $this->decodeNumeric($this->basic_salary_enc ?? null);
    }

    public function setBasicSalaryAttribute(?float $value): void
    {
        $this->attributes['basic_salary_enc'] = $this->encodeNumeric($value);
    }

    public function getFixedAllowancesAttribute(): ?float
    {
        return $this->decodeNumeric($this->fixed_allowances_enc ?? null);
    }

    public function setFixedAllowancesAttribute(?float $value): void
    {
        $this->attributes['fixed_allowances_enc'] = $this->encodeNumeric($value);
    }

    public function getVariableAllowancesAttribute(): ?float
    {
        return $this->decodeNumeric($this->variable_allowances_enc ?? null);
    }

    public function setVariableAllowancesAttribute(?float $value): void
    {
        $this->attributes['variable_allowances_enc'] = $this->encodeNumeric($value);
    }

    public function getOvertimeAttribute(): ?float
    {
        return $this->decodeNumeric($this->overtime_enc ?? null);
    }

    public function setOvertimeAttribute(?float $value): void
    {
        $this->attributes['overtime_enc'] = $this->encodeNumeric($value);
    }

    public function getDeductionsAttribute(): ?float
    {
        return $this->decodeNumeric($this->deductions_enc ?? null);
    }

    public function setDeductionsAttribute(?float $value): void
    {
        $this->attributes['deductions_enc'] = $this->encodeNumeric($value);
    }

    public function getNetPayAttribute(): ?float
    {
        return $this->decodeNumeric($this->net_pay_enc ?? null);
    }

    public function setNetPayAttribute(?float $value): void
    {
        $this->attributes['net_pay_enc'] = $this->encodeNumeric($value);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
