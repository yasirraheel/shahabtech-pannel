<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Never update user last_seen for admin panel requests
        if ($request->is('admin*') || $request->is('api/admin*')) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            
            if ($user && isset($user->id)) {
                $currentIp = getRealIP();
                $now = now();

                if (!$user->last_seen) {
                    $user->last_seen = $now;
                    $user->last_seen_ip = $currentIp;
                    $user->timestamps = false;
                    $user->save();
                    $user->timestamps = true;
                } else {
                    $lastSeen = Carbon::parse($user->last_seen);
                    $diffInSeconds = $lastSeen->diffInSeconds($now);

                    // Update if at least 1 minute (60s) passed or IP changed
                    if ($diffInSeconds >= 60 || $user->last_seen_ip !== $currentIp) {
                        // Accumulate online time if session gap is within 15 minutes (900s)
                        if ($diffInSeconds > 0 && $diffInSeconds <= 900) {
                            $user->total_online_time = (int) ($user->total_online_time ?? 0) + $diffInSeconds;
                        }

                        $user->last_seen = $now;
                        $user->last_seen_ip = $currentIp;

                        // If they have a pending trial, start it now
                        if ($user->pending_trial_minutes > 0) {
                            $user->expires_at = $now->copy()->addMinutes($user->pending_trial_minutes);
                            $user->pending_trial_minutes = null;
                        }

                        $user->timestamps = false;
                        $user->save();
                        $user->timestamps = true;
                    }
                }
            }
        }

        return $next($request);
    }
}
