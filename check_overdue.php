<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$overdue = [];
foreach (\App\Models\Pelanggan::where('status', 1)->get() as $p) {
    if ($p->hasOverdueInvoices()) {
        $overdueInvoices = [];
        foreach ($p->getOverdueInvoices() as $i) {
            $overdueInvoices[] = [
                'no_faktur' => $i->no_faktur,
                'tanggal' => $i->tanggal ? $i->tanggal->toDateString() : null,
                'grand_total' => $i->grand_total,
                'sisa' => $i->grand_total - $i->getApprovedPembayaranTotal() - $i->getTotalRetur()
            ];
        }
        $overdue[] = [
            'kode' => $p->kode_pelanggan,
            'nama' => $p->nama_pelanggan,
            'ljt' => $p->ljt,
            'invoices' => $overdueInvoices
        ];
    }
}

echo json_encode($overdue, JSON_PRETTY_PRINT) . PHP_EOL;
