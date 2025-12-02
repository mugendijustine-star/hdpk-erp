<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use Illuminate\Http\Request;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesTarget::query()->with('salesRep');

        if ($request->filled('month')) {
            $query->where('month', $request->integer('month'));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if ($salesRepId = $request->input('sales_rep_id')) {
            $query->where('sales_rep_id', $salesRepId);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_rep_id' => ['required', 'integer', 'exists:sales_reps,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'target_amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $target = SalesTarget::updateOrCreate(
            [
                'sales_rep_id' => $validated['sales_rep_id'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ],
            [
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $target->target_amount = $validated['target_amount'];
        $target->save();

        return response()->json($target, 201);
    }
}
