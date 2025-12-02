<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'position',
        'branch_id',
        'biometric_id',
        'status',
    ];

    public function getSalaryAttribute(): ?float
    {
        return $this->decodeNumeric($this->salary_enc ?? null);
    }

    public function setSalaryAttribute(?float $value): void
    {
        $this->attributes['salary_enc'] = $this->encodeNumeric($value);
    }

    public function fixedAllowances(): HasMany
    {
        return $this->hasMany(EmployeeFixedAllowance::class);
    }

    public function variableAllowances(): HasMany
    {
        return $this->hasMany(EmployeeVariableAllowance::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceDaily::class);
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function biometricLogs(): HasMany
    {
        return $this->hasMany(BiometricLog::class);
    }
}
