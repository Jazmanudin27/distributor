<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerMobileMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = strtolower(Auth::user()->role ?? '');
            
            // Allow owner, admin, and super admin
            if (!in_array($role, ['owner', 'admin', 'super admin', 'superadmin'])) {
                Auth::logout();
                return redirect()->route('mobile.owner.login')->with('error', 'Hanya Owner/Admin yang memiliki akses ke halaman ini.');
            }
        } else {
            return redirect()->route('mobile.owner.login');
        }

        return $next($request);
    }
}
