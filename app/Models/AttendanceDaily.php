<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDaily extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

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
        'hours_worked_enc',
        'late_minutes_enc',
        'overtime_minutes_enc',
    ];

    public function getHoursWorkedAttribute(): ?float
    {
        return $this->decodeNumeric($this->hours_worked_enc ?? null);
    }

    public function setHoursWorkedAttribute(?float $value): void
    {
        $this->attributes['hours_worked_enc'] = $this->encodeNumeric($value);
    }

    public function getLateMinutesAttribute(): ?float
    {
        return $this->decodeNumeric($this->late_minutes_enc ?? null);
    }

    public function setLateMinutesAttribute(?float $value): void
    {
        $this->attributes['late_minutes_enc'] = $this->encodeNumeric($value);
    }

    public function getOvertimeMinutesAttribute(): ?float
    {
        return $this->decodeNumeric($this->overtime_minutes_enc ?? null);
    }

    public function setOvertimeMinutesAttribute(?float $value): void
    {
        $this->attributes['overtime_minutes_enc'] = $this->encodeNumeric($value);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
