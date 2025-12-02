<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckTrustedDevice
{
    public function handle(Request $request, Closure $next)
    {
        $fingerprint = $request->header('X-Device-Fingerprint');

        if (! $fingerprint) {
            return response()->json([
                'message' => 'Device fingerprint missing.',
            ], 403);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $trustedDevice = DB::table('trusted_devices')
            ->where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->first();

        if (! $trustedDevice) {
            return response()->json([
                'message' => 'Device not authorized.',
            ], 403);
        }

        return $next($request);
    }
}
