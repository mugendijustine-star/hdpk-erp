<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDaily;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = AttendanceDaily::query();

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        $attendance = $query->get();

        return response()->json($attendance);
    }

    /**
     * Store or update attendance record for an employee on a given date.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'hours_worked' => ['nullable', 'numeric'],
            'late_minutes' => ['nullable', 'numeric'],
            'overtime_minutes' => ['nullable', 'numeric'],
        ]);

        $attendance = AttendanceDaily::firstOrNew([
            'employee_id' => $data['employee_id'],
            'date' => $data['date'],
        ]);

        $attendance->employee_id = $data['employee_id'];
        $attendance->date = $data['date'];
        $attendance->time_in = $data['time_in'] ?? null;
        $attendance->time_out = $data['time_out'] ?? null;

        foreach (['hours_worked', 'late_minutes', 'overtime_minutes'] as $field) {
            if (array_key_exists($field, $data)) {
                $attendance->{$field} = $data[$field];
            }
        }

        $attendance->save();

        return response()->json($attendance);
    }
}
