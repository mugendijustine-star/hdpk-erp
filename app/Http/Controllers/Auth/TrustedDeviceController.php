<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrustedDeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:system.manage.devices');
    }

    public function index(User $user)
    {
        return response()->json($user->trustedDevices);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'device_fingerprint' => [
                'required',
                'string',
                Rule::unique('trusted_devices')->where(fn ($query) => $query->where('user_id', $request->input('user_id'))),
            ],
            'device_name' => ['nullable', 'string'],
        ]);

        $device = TrustedDevice::create([
            'user_id' => $data['user_id'],
            'device_fingerprint' => $data['device_fingerprint'],
            'device_name' => $data['device_name'] ?? null,
        ]);

        return response()->json([
            'message' => 'Trusted device created successfully.',
            'device' => $device,
        ], 201);
    }

    public function deactivate(TrustedDevice $device)
    {
        $device->update(['is_active' => false]);

        return response()->json([
            'message' => 'Trusted device deactivated.',
            'device' => $device,
        ]);
    }
}
