<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tanggal_dari = '2026-06-01'; // or whatever dates are relevant
$tanggal_sampai = '2026-06-30';

echo "--- TESTING LABA RUGI DETAIL TOTALS ---\n";
$detailQuery = DB::table('penjualan_detail as d')
    ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
    ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
    ->join('barang_satuan as s', 's.id', '=', 'd.satuan_id')
    ->join('supplier as sup', 'sup.kode_supplier', '=', 'b.kode_supplier')
    ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
    ->where('p.batal', 0);

$totalDetailBruto = $detailQuery->sum(DB::raw('d.qty * d.harga'));
$totalDetailDiskon = $detailQuery->sum('d.total_diskon');
$totalDetailNet = $totalDetailBruto - $totalDetailDiskon;

// Calculate HPP (excluding promo in Detail loop)
$detailRows = $detailQuery->select('d.is_promo', 'd.qty', 'd.harga_pokok')->get();
$totalDetailHpp = 0;
foreach ($detailRows as $r) {
    if ($r->is_promo != 1) {
        $totalDetailHpp += $r->qty * $r->harga_pokok;
    }
}

echo "Detail Bruto: " . number_format($totalDetailBruto) . "\n";
echo "Detail Diskon: " . number_format($totalDetailDiskon) . "\n";
echo "Detail Net: " . number_format($totalDetailNet) . "\n";
echo "Detail HPP (ex promo): " . number_format($totalDetailHpp) . "\n\n";

echo "--- TESTING LABA RUGI PER SUPPLIER TOTALS ---\n";
// Let's get all suppliers
$suppliers = DB::table('supplier')->where('status', 1)->get();
$totalSupBruto = 0;
$totalSupDiskon = 0;
$totalSupHpp = 0;

foreach ($suppliers as $sup) {
    $salesData = DB::table('penjualan_detail as d')
        ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
        ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
        ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
        ->where('b.kode_supplier', $sup->kode_supplier)
        ->where('d.is_promo', 0)
        ->where('p.batal', 0)
        ->selectRaw('SUM(d.qty * d.harga) as bruto, SUM(d.total_diskon) as diskon')
        ->first();

    $supBruto = (float)($salesData->bruto ?? 0);
    $supDiskon = (float)($salesData->diskon ?? 0);

    $total_hpp = DB::table('penjualan_detail as d')
        ->join('barang as b', 'b.kode_barang', '=', 'd.kode_barang')
        ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
        ->whereBetween('p.tanggal', [$tanggal_dari, $tanggal_sampai])
        ->where('b.kode_supplier', $sup->kode_supplier)
        ->where('d.is_promo', 0)
        ->where('p.batal', 0)
        ->sum(DB::raw('d.harga_pokok * d.qty'));

    $totalSupBruto += $supBruto;
    $totalSupDiskon += $supDiskon;
    $totalSupHpp += $total_hpp;
}

echo "Supplier Bruto (ex promo): " . number_format($totalSupBruto) . "\n";
echo "Supplier Diskon (ex promo): " . number_format($totalSupDiskon) . "\n";
echo "Supplier Net (ex promo): " . number_format($totalSupBruto - $totalSupDiskon) . "\n";
echo "Supplier HPP (ex promo): " . number_format($totalSupHpp) . "\n";
