<?php

namespace App\Http\Middleware;

use App\Models\TrustedDevice;
use Closure;
use Illuminate\Http\Request;

class EnsureTrustedDevice
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $fingerprint = $request->header('X-Device-Fingerprint');

        $trustedDevice = TrustedDevice::query()
            ->where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->where('is_active', true)
            ->first();

        if (! $fingerprint || ! $trustedDevice) {
            abort(403, 'Device not authorized. Contact admin.');
        }

        $trustedDevice->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
