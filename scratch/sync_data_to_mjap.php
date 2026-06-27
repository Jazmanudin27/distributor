<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Starting data sync from multiple sources to 'mjap'...\n";

    Schema::disableForeignKeyConstraints();

    // 1. Sync Supplier from 'mjap2.supplier'
    echo " - Syncing 'supplier' from mjap2...\n";
    $suppliers = DB::connection('mysql')->table('mjap2.supplier')->get();
    foreach ($suppliers as $sup) {
        DB::table('supplier')->updateOrInsert(
            ['kode_supplier' => $sup->kode_supplier],
            (array)$sup
        );
    }
    $supCount = DB::table('supplier')->count();
    echo "   Done: $supCount suppliers synced.\n";

    // 2. Sync Kategori (if exists in hajiaah)
    $hasKategori = !empty(DB::select("SHOW TABLES FROM hajiaah LIKE 'kategori'"));
    if ($hasKategori) {
        echo " - Syncing 'kategori' from hajiaah...\n";
        $kategoris = DB::connection('mysql')->table('hajiaah.kategori')->get();
        foreach ($kategoris as $kat) {
            DB::table('kategori')->updateOrInsert(
                ['id' => $kat->id],
                (array)$kat
            );
        }
        $katCount = DB::table('kategori')->count();
        echo "   Done: $katCount kategori synced.\n";
    }

    // 3. Sync Merk (if exists in hajiaah)
    $hasMerk = !empty(DB::select("SHOW TABLES FROM hajiaah LIKE 'merk'"));
    if ($hasMerk) {
        echo " - Syncing 'merk' from hajiaah...\n";
        $merks = DB::connection('mysql')->table('hajiaah.merk')->get();
        foreach ($merks as $m) {
            DB::table('merk')->updateOrInsert(
                ['id' => $m->id],
                (array)$m
            );
        }
        $merkCount = DB::table('merk')->count();
        echo "   Done: $merkCount merk synced.\n";
    }

    // 4. Sync Barang from 'hajiaah.barang'
    echo " - Syncing 'barang' from hajiaah...\n";
    $barangs = DB::connection('mysql')->table('hajiaah.barang')->get();
    foreach ($barangs as $brg) {
        // Ensure any foreign key fields are clean
        DB::table('barang')->updateOrInsert(
            ['kode_barang' => $brg->kode_barang],
            (array)$brg
        );
    }
    $brgCount = DB::table('barang')->count();
    echo "   Done: $brgCount barang synced.\n";

    // 5. Sync Barang Satuan from 'hajiaah.barang_satuan'
    $hasSatuan = !empty(DB::select("SHOW TABLES FROM hajiaah LIKE 'barang_satuan'"));
    if ($hasSatuan) {
        echo " - Syncing 'barang_satuan' from hajiaah...\n";
        $satuans = DB::connection('mysql')->table('hajiaah.barang_satuan')->get();
        foreach ($satuans as $sat) {
            DB::table('barang_satuan')->updateOrInsert(
                ['id' => $sat->id],
                (array)$sat
            );
        }
        $satCount = DB::table('barang_satuan')->count();
        echo "   Done: $satCount barang_satuan synced.\n";
    }

    Schema::enableForeignKeyConstraints();

    echo "Sync completed successfully!\n";

} catch (\Exception $e) {
    Schema::enableForeignKeyConstraints();
    echo "Error during sync: " . $e->getMessage() . "\n";
}
