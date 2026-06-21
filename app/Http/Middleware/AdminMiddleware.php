<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = strtolower(Auth::user()->role ?? '');
            
            // Jika user adalah sales, larang akses ke admin panel
            if (in_array($role, ['sales'])) {
                return redirect()->route('mobile.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman admin/desktop.');
            }
        }

        return $next($request);
    }
}
