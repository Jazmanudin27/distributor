<?php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$today = Carbon::today();
$todayTs = $today->timestamp;

echo "Hari ini: " . $today->format('Y-m-d') . " (ts: $todayTs)\n\n";

$invs = DB::table('penjualan')
    ->where('batal', 0)
    ->where('jenis_transaksi', 'K')
    ->whereRaw('DATEDIFF(NOW(), tanggal) > 900')
    ->select('no_faktur', 'tanggal', 'kode_pelanggan', 'grand_total')
    ->limit(10)
    ->get();

echo "Faktur kredit > 900 hari lalu: " . $invs->count() . "\n\n";

foreach ($invs as $inv) {
    $ljt = DB::table('pelanggan')
        ->where('kode_pelanggan', $inv->kode_pelanggan)
        ->value('ljt') ?? 30;

    $jatuh_tempo = Carbon::parse($inv->tanggal)->addDays($ljt)->startOfDay();
    $diff = (int) floor(($todayTs - $jatuh_tempo->timestamp) / 86400);

    $bucket = 'Belum JT';
    if ($diff > 0) {
        if ($diff <= 30)
            $bucket = '1-30 Hari';
        elseif ($diff <= 60)
            $bucket = '31-60 Hari';
        elseif ($diff <= 90)
            $bucket = '61-90 Hari';
        else
            $bucket = '> 90 Hari';
    }

    echo "Faktur: {$inv->no_faktur}\n";
    echo "  Tanggal: {$inv->tanggal} | LJT: {$ljt} hari\n";
    echo "  Jatuh Tempo: " . $jatuh_tempo->format('Y-m-d') . " (ts: " . $jatuh_tempo->timestamp . ")\n";
    echo "  Diff: {$diff} hari\n";
    echo "  Bucket: {$bucket}\n\n";
}
