<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeVariableAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'month',
        'year',
        'status',
        'entered_by',
        'approved_by',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'amount' => 'float',
        'month' => 'integer',
        'year' => 'integer',
        'entered_by' => 'integer',
        'approved_by' => 'integer',
    ];
}
