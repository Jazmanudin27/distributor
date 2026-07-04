<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckPenjualanLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if ($type === 'admin') {
            if (Setting::getVal('lock_penjualan_admin', '0') === '1') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Input dan edit penjualan untuk Admin sedang dikunci sementara oleh Owner/System.'
                    ], 403);
                }
                return redirect()->route('penjualan.index')->with('error', 'Input dan edit penjualan untuk Admin sedang dikunci sementara oleh Owner/System.');
            }
        } elseif ($type === 'sales') {
            if (Setting::getVal('lock_penjualan_sales', '0') === '1') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Input penjualan untuk Sales sedang dikunci sementara oleh Owner/System.'
                    ], 403);
                }
                return redirect()->route('mobile.dashboard')->with('error', 'Input penjualan untuk Sales sedang dikunci sementara oleh Owner/System.');
            }
        }

        return $next($request);
    }
}
