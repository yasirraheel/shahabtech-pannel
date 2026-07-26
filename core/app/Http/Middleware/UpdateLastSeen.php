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
        if (Auth::check()) {
            $user = Auth::user();
            $currentIp = getRealIP();
            $now = now();
            $lastSeen = $user->last_seen ? Carbon::parse($user->last_seen) : null;

            // Update if last_seen is null, at least 15 seconds have passed, or IP changed
            if (!$lastSeen || $now->diffInSeconds($lastSeen) >= 15 || $user->last_seen_ip !== $currentIp) {
                if ($lastSeen) {
                    $diffInSeconds = $now->diffInSeconds($lastSeen);
                    // Only accumulate if session gap is within 15 minutes (900 seconds)
                    if ($diffInSeconds > 0 && $diffInSeconds <= 900) {
                        $user->total_online_time = ($user->total_online_time ?? 0) + $diffInSeconds;
                    }
                }

                $user->last_seen = $now;
                $user->last_seen_ip = $currentIp;
                
                // If they have a pending trial, start it now
                if ($user->pending_trial_minutes > 0) {
                    $user->expires_at = $now->copy()->addMinutes($user->pending_trial_minutes);
                    $user->pending_trial_minutes = null;
                }

                // To avoid triggering standard updated_at column or other events if we just want to update this quietly
                $user->timestamps = false;
                $user->save();
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}
