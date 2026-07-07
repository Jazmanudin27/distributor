<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill approved_at untuk data canvas_sessions lama yang masih NULL.
     *
     * Logika:
     * - status = 'loading'   → approved_at = updated_at
     *   Karena satu-satunya aksi yang mengubah status ke 'loading' adalah approve(),
     *   sehingga updated_at ≈ waktu approve. Session yang di-edit setelah approve
     *   akan punya updated_at yang berbeda, tapi ini trade-off terbaik untuk data lama.
     *
     * - status = 'completed' → approved_at = created_at (fallback aman)
     *   updated_at pada completed session = waktu setoran, BUKAN waktu approve.
     *   Fallback ke created_at mempertahankan perilaku lama (query faktur dari awal session).
     *
     * - status = 'pending'   → biarkan NULL (belum di-approve, logic sudah benar)
     */
    public function up(): void
    {
        // Loading sessions: approved_at = updated_at (estimasi waktu approve)
        DB::table('canvas_sessions')
            ->whereNull('approved_at')
            ->where('status', 'loading')
            ->update(['approved_at' => DB::raw('updated_at')]);

        // Completed sessions: approved_at = created_at (fallback, waktu approve tidak diketahui)
        DB::table('canvas_sessions')
            ->whereNull('approved_at')
            ->where('status', 'completed')
            ->update(['approved_at' => DB::raw('created_at')]);
    }

    /**
     * Rollback: tidak bisa tahu mana yang di-backfill vs yang asli,
     * jadi reset semua ke NULL kecuali yang created_at != approved_at (asli dari approve()).
     * Aman: set semua NULL, lalu re-run up() jika perlu.
     */
    public function down(): void
    {
        // Hanya reset data lama (approved_at = created_at artinya hasil backfill completed)
        // Data baru dari approve() akan punya approved_at > created_at
        DB::table('canvas_sessions')
            ->where('status', 'completed')
            ->whereColumn('approved_at', 'created_at')
            ->update(['approved_at' => null]);
    }
};
