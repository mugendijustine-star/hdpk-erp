<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDaily extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'late_minutes',
        'overtime_minutes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected function obfuscate(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return $value / 3 + 5;
    }

    protected function deobfuscate(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return ($value - 5) * 3;
    }

    public function setHoursWorkedAttribute($value): void
    {
        $this->attributes['hours_worked'] = $this->obfuscate($value !== null ? (float) $value : null);
    }

    public function getHoursWorkedAttribute($value): ?float
    {
        return $this->deobfuscate($value !== null ? (float) $value : null);
    }

    public function setLateMinutesAttribute($value): void
    {
        $this->attributes['late_minutes'] = $this->obfuscate($value !== null ? (float) $value : null);
    }

    public function getLateMinutesAttribute($value): ?float
    {
        return $this->deobfuscate($value !== null ? (float) $value : null);
    }

    public function setOvertimeMinutesAttribute($value): void
    {
        $this->attributes['overtime_minutes'] = $this->obfuscate($value !== null ? (float) $value : null);
    }

    public function getOvertimeMinutesAttribute($value): ?float
    {
        return $this->deobfuscate($value !== null ? (float) $value : null);
    }
}
