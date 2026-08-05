<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach ([3306, 3307] as $port) {
    config(['database.connections.mysql.port' => $port]);
    config(['database.connections.mysql.database' => 'information_schema']);
    try {
        DB::purge('mysql');
        DB::reconnect('mysql');
        $dbs = DB::table('SCHEMATA')->pluck('SCHEMA_NAME')->toArray();
        echo "Port $port databases: " . implode(', ', $dbs) . "\n";
        
        foreach ($dbs as $db) {
            if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
            config(['database.connections.mysql.database' => $db]);
            DB::purge('mysql');
            DB::reconnect('mysql');
            
            $tables = DB::select("SHOW TABLES LIKE 'penjualan'");
            if (!empty($tables)) {
                $count = DB::table('penjualan')->count();
                echo " -> DB '$db' on port $port has $count penjualan rows.\n";
                if ($count > 0) {
                    $penjualans = DB::select("
                        SELECT 
                            p.no_faktur,
                            p.grand_total,
                            p.total,
                            p.diskon,
                            COALESCE(cb.cash_total, 0) as cash_total,
                            COALESCE(tr.transfer_total, 0) as transfer_total,
                            COALESCE(gr.giro_total, 0) as giro_total,
                            COALESCE(rt.retur_total, 0) as retur_total,
                            (COALESCE(cb.cash_total, 0) + COALESCE(tr.transfer_total, 0) + COALESCE(gr.giro_total, 0)) as total_bayar,
                            (p.grand_total - (COALESCE(cb.cash_total, 0) + COALESCE(tr.transfer_total, 0) + COALESCE(gr.giro_total, 0)) - COALESCE(rt.retur_total, 0)) as sisa
                        FROM penjualan p
                        LEFT JOIN (SELECT no_faktur, SUM(jumlah) as cash_total FROM penjualan_pembayaran WHERE status = 'disetujui' GROUP BY no_faktur) cb ON p.no_faktur = cb.no_faktur
                        LEFT JOIN (SELECT no_faktur, SUM(jumlah) as transfer_total FROM penjualan_pembayaran_transfer WHERE status = 'disetujui' GROUP BY no_faktur) tr ON p.no_faktur = tr.no_faktur
                        LEFT JOIN (SELECT no_faktur, SUM(jumlah) as giro_total FROM penjualan_pembayaran_giro WHERE status = 'disetujui' GROUP BY no_faktur) gr ON p.no_faktur = gr.no_faktur
                        LEFT JOIN (SELECT no_faktur, SUM(total) as retur_total FROM retur_penjualan GROUP BY no_faktur) rt ON p.no_faktur = rt.no_faktur
                        WHERE ABS(p.grand_total - (COALESCE(cb.cash_total, 0) + COALESCE(tr.transfer_total, 0) + COALESCE(gr.giro_total, 0)) - COALESCE(rt.retur_total, 0)) > 0.001
                          AND ABS(p.grand_total - (COALESCE(cb.cash_total, 0) + COALESCE(tr.transfer_total, 0) + COALESCE(gr.giro_total, 0)) - COALESCE(rt.retur_total, 0)) <= 1000
                        ORDER BY p.tanggal DESC
                        LIMIT 30
                    ");

                    echo "  Found " . count($penjualans) . " records with small diff (0.001 to 1000):\n";
                    foreach ($penjualans as $row) {
                        echo "  Faktur: {$row->no_faktur} | GT: {$row->grand_total} | Bayar: {$row->total_bayar} | Retur: {$row->retur_total} | Sisa: {$row->sisa}\n";
                    }
                }
            }
        }
    } catch (\Exception $e) {
        echo "Port $port error: " . $e->getMessage() . "\n";
    }
}
