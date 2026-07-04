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
            // Update last seen if it's null or older than 1 minute to reduce DB queries
            if (!$user->last_seen || Carbon::parse($user->last_seen)->diffInMinutes(now()) >= 1) {
                $user->last_seen = now();
                // To avoid triggering standard updated_at column or other events if we just want to update this quietly
                $user->timestamps = false;
                $user->save();
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}
