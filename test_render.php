<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\Barang;

try {
    $item = Pembelian::with(['details.barang'])->first();
    if (!$item) {
        echo "No Pembelian record found!\n";
        exit;
    }
    echo "Testing with Faktur: " . $item->no_faktur . "\n";
    $suppliers = Supplier::where('status', 1)->get();
    $barangs = Barang::where('status', 1)->with('satuans')->get();
    
    $html = view('pembelian.form', compact('item', 'suppliers', 'barangs'))->render();
    echo "Rendered successfully! Length: " . strlen($html) . "\n";
    
    // Find the javascript part with existingDetails
    if (preg_match('/const existingDetails = (.*?);/', $html, $matches)) {
        echo "existingDetails JSON:\n" . substr($matches[1], 0, 500) . "...\n";
    } else {
        echo "Could not find existingDetails in HTML!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
