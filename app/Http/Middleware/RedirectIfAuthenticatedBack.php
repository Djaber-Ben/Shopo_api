<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedBack
{
    public function handle(Request $request, Closure $next)
    {
        // If user is already logged in
        if (Auth::check()) {
            // Get previous URL
            $previous = url()->previous();

            // If coming *from* login page or no referrer → go to dashboard
            if ($previous === $request->fullUrl() || $previous === route('admin.login')) {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'You are already logged in.');
            }

            // Otherwise, go back to where they came from
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
