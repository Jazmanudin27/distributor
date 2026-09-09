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
            $lockStatus = self::isSalesLocked();
            if ($lockStatus['locked']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => $lockStatus['message']
                    ], 403);
                }
                return redirect()->route('mobile.dashboard')->with('error', $lockStatus['message']);
            }
        }

        return $next($request);
    }

    /**
     * Check if a given time falls within the start and end time window.
     */
    public static function isTimeInWindow(string $currentTime, string $startTime, string $endTime): bool
    {
        if ($startTime === $endTime) {
            return false;
        }

        if ($startTime < $endTime) {
            return $currentTime >= $startTime && $currentTime < $endTime;
        }

        // Window crosses midnight (e.g. 19:00 to 06:00)
        return $currentTime >= $startTime || $currentTime < $endTime;
    }

    /**
     * Helper to check if sales input is currently locked (manual or schedule).
     */
    public static function isSalesLocked(): array
    {
        // 1. Manual Lock
        if (Setting::getVal('lock_penjualan_sales', '0') === '1') {
            return [
                'locked' => true,
                'reason' => 'manual',
                'message' => 'Input penjualan untuk Sales sedang dikunci sementara oleh Owner/System.'
            ];
        }

        // 2. Automated Schedule Lock (Default: 19:00 - 06:00 WIB)
        $autoLock = Setting::getVal('auto_lock_penjualan_sales', '1') === '1';
        if ($autoLock) {
            $startTime = Setting::getVal('lock_sales_start', '19:00');
            $endTime = Setting::getVal('lock_sales_end', '06:00');
            $currentTime = now()->format('H:i');

            if (self::isTimeInWindow($currentTime, $startTime, $endTime)) {
                return [
                    'locked' => true,
                    'reason' => 'schedule',
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'message' => "Input penjualan untuk Sales sedang ditutup (Jam operasional ditutup pukul {$startTime} dan dibuka kembali pukul {$endTime} WIB)."
                ];
            }
        }

        return [
            'locked' => false,
            'reason' => null,
            'message' => null
        ];
    }
}

