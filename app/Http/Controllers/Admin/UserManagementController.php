<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesRep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('salesRep');

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'has_sales_rep' => $user->salesRep !== null,
                ];
            });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'in:admin,manager,clerk,sales_rep'],
            'sales_rep' => ['sometimes', 'array'],
            'sales_rep.name' => ['sometimes', 'string'],
            'sales_rep.phone' => ['sometimes', 'string', 'nullable'],
            'sales_rep.email' => ['sometimes', 'email', 'nullable'],
            'sales_rep.region' => ['sometimes', 'string', 'nullable'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($user->isSalesRep() && isset($validated['sales_rep'])) {
            $salesRepData = $validated['sales_rep'];

            SalesRep::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $salesRepData['name'] ?? $user->name,
                    'phone' => $salesRepData['phone'] ?? null,
                    'email' => $salesRepData['email'] ?? $user->email,
                    'region' => $salesRepData['region'] ?? null,
                ]
            );
        }

        return response()->json($user->fresh('salesRep'));
    }

    public function update(User $user, Request $request)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', "unique:users,email,{$user->id}"],
            'password' => ['sometimes', 'min:6'],
            'role' => ['sometimes', 'in:admin,manager,clerk,sales_rep'],
            'sales_rep' => ['sometimes', 'array'],
            'sales_rep.name' => ['sometimes', 'string'],
            'sales_rep.phone' => ['sometimes', 'string', 'nullable'],
            'sales_rep.email' => ['sometimes', 'email', 'nullable'],
            'sales_rep.region' => ['sometimes', 'string', 'nullable'],
        ]);

        $originalRole = $user->role;

        if (array_key_exists('password', $validated)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->fill($validated);
        $user->save();

        $user->refresh();

        if ($user->isSalesRep() && !$user->salesRep) {
            $salesRepData = $validated['sales_rep'] ?? [];

            SalesRep::create([
                'user_id' => $user->id,
                'name' => $salesRepData['name'] ?? $user->name,
                'phone' => $salesRepData['phone'] ?? null,
                'email' => $salesRepData['email'] ?? $user->email,
                'region' => $salesRepData['region'] ?? null,
            ]);
        } elseif ($originalRole === 'sales_rep' && !$user->isSalesRep()) {
            // Keeping the existing SalesRep record attached; it will no longer be used for role-based flows.
        }

        return response()->json($user->load('salesRep'));
    }

    public function linkSalesRep(User $user, Request $request)
    {
        $validated = $request->validate([
            'sales_rep_id' => ['sometimes', 'integer', 'exists:sales_reps,id'],
            'name' => ['sometimes', 'string'],
            'phone' => ['sometimes', 'string', 'nullable'],
            'email' => ['sometimes', 'email', 'nullable'],
            'region' => ['sometimes', 'string', 'nullable'],
        ]);

        if (isset($validated['sales_rep_id'])) {
            $salesRep = SalesRep::findOrFail($validated['sales_rep_id']);
            $salesRep->user_id = $user->id;
            $salesRep->save();
        } else {
            $salesRep = SalesRep::create([
                'user_id' => $user->id,
                'name' => $validated['name'] ?? $user->name,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? $user->email,
                'region' => $validated['region'] ?? null,
            ]);
        }

        return response()->json($user->load('salesRep'));
    }
}
