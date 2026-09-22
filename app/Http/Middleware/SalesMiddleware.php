<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SalesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = strtolower($user->role ?? '');
            
            // Jika user bukan sales / spv sales, arahkan ke halaman utama desktop
            if (!in_array($role, ['sales', 'spv sales', 'spv sales 1', 'spv sales 2']) && !$user->isSpv1() && !$user->isSpv2()) {
                return redirect('/')->with('error', 'Hanya sales/spv sales yang memiliki akses ke halaman mobile.');
            }
        }

        return $next($request);
    }
}
