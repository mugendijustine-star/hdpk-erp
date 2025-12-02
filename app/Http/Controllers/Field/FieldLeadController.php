<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\FieldLead;
use Illuminate\Http\Request;

class FieldLeadController extends Controller
{
    public function index(Request $request)
    {
        $query = FieldLead::query()->with(['salesRep', 'customer']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($salesRepId = $request->input('sales_rep_id')) {
            $query->where('sales_rep_id', $salesRepId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_rep_id' => ['required', 'integer', 'exists:sales_reps,id'],
            'name' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'source' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'expected_value' => ['nullable', 'numeric'],
        ]);

        $lead = new FieldLead($validated);

        if (array_key_exists('expected_value', $validated)) {
            $lead->expected_value = $validated['expected_value'];
        }

        $lead->status = $validated['status'] ?? 'new';
        $lead->save();

        return response()->json($lead, 201);
    }

    public function updateStatus(FieldLead $lead, Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,converted,closed'],
        ]);

        $lead->update(['status' => $validated['status']]);

        return response()->json($lead);
    }
}
