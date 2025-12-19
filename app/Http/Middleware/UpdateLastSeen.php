<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Only update if last_seen is null or older than 1 minute
            // This reduces database writes while still tracking activity
            if (!$user->last_seen || $user->last_seen->lt(now()->subMinute())) {
                $user->update(['last_seen' => now()]);
            }
        }

        return $next($request);
    }
}
