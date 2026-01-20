<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has super_admin role
        if (auth()->user()->role !== 'super_admin') {
            // Redirect non-admins to their user dashboard
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
