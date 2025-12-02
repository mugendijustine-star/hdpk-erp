<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\FieldVisit;
use Illuminate\Http\Request;

class FieldVisitController extends Controller
{
    public function index(Request $request)
    {
        $query = FieldVisit::query()->with(['salesRep', 'customer', 'territory']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        if ($salesRepId = $request->input('sales_rep_id')) {
            $query->where('sales_rep_id', $salesRepId);
        }

        if ($territoryId = $request->input('territory_id')) {
            $query->where('sales_territory_id', $territoryId);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $visits = $query->get()->map(function (FieldVisit $visit) {
            return [
                'id' => $visit->id,
                'date' => $visit->date,
                'sales_rep' => optional($visit->salesRep)->name,
                'customer' => optional($visit->customer)->name,
                'territory' => optional($visit->territory)->name,
                'purpose' => $visit->purpose,
                'notes' => $visit->notes,
            ];
        });

        return response()->json($visits);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_rep_id' => ['required', 'integer', 'exists:sales_reps,id'],
            'date' => ['required', 'date'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'sales_territory_id' => ['required', 'integer', 'exists:sales_territories,id'],
            'purpose' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $validated['created_by'] = auth()->id();

        $visit = FieldVisit::create($validated);

        return response()->json($visit, 201);
    }

    public function show(FieldVisit $visit)
    {
        $visit->load(['salesRep', 'customer', 'territory', 'createdBy']);

        return response()->json($visit);
    }
}
