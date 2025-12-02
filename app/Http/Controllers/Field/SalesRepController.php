<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\SalesRep;
use App\Models\SalesTerritory;
use Illuminate\Http\Request;

class SalesRepController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesRep::query();

        if ($region = $request->input('region')) {
            $query->where('region', $region);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reps = $query->get(['id', 'name', 'phone', 'email', 'region', 'status', 'user_id']);

        return response()->json($reps);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['required', 'email'],
            'region' => ['required', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string'],
        ]);

        $rep = SalesRep::updateOrCreate(
            ['email' => $validated['email']],
            $validated
        );

        return response()->json($rep, 201);
    }

    public function show(SalesRep $rep)
    {
        $rep->load('territories');

        return response()->json($rep);
    }

    public function attachTerritories(SalesRep $rep, Request $request)
    {
        $validated = $request->validate([
            'territory_ids' => ['required', 'array'],
            'territory_ids.*' => ['integer', 'exists:sales_territories,id'],
        ]);

        $territoryIds = $validated['territory_ids'] ?? [];
        $rep->territories()->sync($territoryIds);

        return response()->json([
            'message' => 'Territories updated successfully.',
            'territories' => SalesTerritory::whereIn('id', $territoryIds)->get(),
        ]);
    }
}
