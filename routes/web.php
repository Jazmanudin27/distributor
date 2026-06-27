<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MerkController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangSatuanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ReturPenjualanController;
use App\Http\Controllers\AjuanLimitKreditController;
use App\Http\Controllers\DiskonStrataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenjualanKirimanController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\SalesTrackingController;
use App\Http\Controllers\LaporanPembelianController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\KeuanganMutasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CanvasController;

// Mobile Controllers
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileKunjunganController;
use App\Http\Controllers\Mobile\MobileOrderController;
use App\Http\Controllers\Mobile\MobileAjuanLimitController;
use App\Http\Controllers\Mobile\MobileOwnerController;
use App\Http\Controllers\Mobile\MobilePelangganController;

Route::pattern('no_faktur', '[A-Za-z0-9_/-]+');
Route::pattern('penjualan', '[A-Za-z0-9_/-]+');
Route::pattern('no_retur', '[A-Za-z0-9_/-]+');
Route::pattern('retur_penjualan', '[A-Za-z0-9_/-]+');
Route::pattern('pembelian', '[A-Za-z0-9_/-]+');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Guest Mobile Routes (Unified Login) ───────────────────────────────────
Route::get('/m/login', [MobileAuthController::class, 'showLoginForm'])->name('mobile.login');
Route::post('/m/login', [MobileAuthController::class, 'unifiedLogin'])->name('mobile.login.unified');
Route::post('/m/logout', [MobileAuthController::class, 'logout'])->name('mobile.logout');
Route::post('/m/owner/logout', [MobileAuthController::class, 'ownerLogout'])->name('mobile.owner.logout');

// Backward compat — /m/owner/login redirects to unified login
Route::get('/m/owner/login', function () {
    return redirect()->route('mobile.login');
})->name('mobile.owner.login');
Route::post('/m/owner/login', [MobileAuthController::class, 'ownerLogin']);

Route::middleware('auth')->group(function () {
    // Authenticated Mobile Routes
    Route::prefix('m')->name('mobile.')->middleware('sales')->group(function () {
        Route::get('/', [MobileDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [MobileDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [ProfileController::class, 'updateMobile'])->name('profile.update-credentials');

        // Barang / Stok
        Route::get('/barang', [\App\Http\Controllers\Mobile\MobileBarangController::class, 'index'])->name('barang.index');

        // Kunjungan (Visits)
        Route::get('/kunjungan', [MobileKunjunganController::class, 'index'])->name('kunjungan.index');
        Route::post('/kunjungan/checkin', [MobileKunjunganController::class, 'checkin'])->name('kunjungan.checkin');
        Route::post('/kunjungan/checkout', [MobileKunjunganController::class, 'checkout'])->name('kunjungan.checkout');

        // Orders (Sales Regular)
        Route::get('/order', [MobileOrderController::class, 'index'])->name('order.index');
        Route::get('/order/create', [MobileOrderController::class, 'create'])->name('order.create');
        Route::post('/order/store', [MobileOrderController::class, 'store'])->name('order.store');
        Route::post('/order/{no_faktur}/payment', [MobileOrderController::class, 'storePayment'])->name('order.payment');

        // Orders (Sales Canvas) — tanpa check-in, pelanggan otomatis dari user
        Route::get('/order/canvas', [MobileOrderController::class, 'createCanvas'])->name('order.canvas.create');
        Route::post('/order/canvas', [MobileOrderController::class, 'storeCanvas'])->name('order.canvas.store');
        Route::get('/order/canvas/dpb', [MobileOrderController::class, 'canvasDpb'])->name('order.canvas.dpb');
        Route::get('/order/canvas/dpb/create', [MobileOrderController::class, 'createCanvasDpb'])->name('order.canvas.dpb.create');
        Route::post('/order/canvas/dpb', [MobileOrderController::class, 'storeCanvasDpb'])->name('order.canvas.dpb.store');

        // Ajuan Limit Kredit
        Route::get('/limit-kredit', [MobileAjuanLimitController::class, 'index'])->name('limit-kredit.index');
        Route::get('/limit-kredit/create', [MobileAjuanLimitController::class, 'create'])->name('limit-kredit.create');
        Route::post('/limit-kredit/store', [MobileAjuanLimitController::class, 'store'])->name('limit-kredit.store');
        Route::post('/spv/limit-kredit/{id}/approve', [MobileAjuanLimitController::class, 'approveSpv'])->name('spv.limit-kredit.approve');
        Route::post('/spv/limit-kredit/{id}/reject', [MobileAjuanLimitController::class, 'rejectSpv'])->name('spv.limit-kredit.reject');

        // Pelanggan (Customers)
        Route::get('/pelanggan/create', [MobilePelangganController::class, 'create'])->name('pelanggan.create');
        Route::post('/pelanggan/store', [MobilePelangganController::class, 'store'])->name('pelanggan.store');
        
        // SPV Sales Customer Approvals
        Route::get('/spv/pelanggan-pending', [MobilePelangganController::class, 'pendingListSpv'])->name('spv.pelanggan.pending');
        Route::post('/spv/approve-pelanggan/{kode_pelanggan}', [MobilePelangganController::class, 'approveSpv'])->name('spv.pelanggan.approve');
        Route::post('/spv/reject-pelanggan/{kode_pelanggan}', [MobilePelangganController::class, 'rejectSpv'])->name('spv.pelanggan.reject');

        // SPV Sales Monitoring
        Route::get('/spv/sales-achievement', [MobileDashboardController::class, 'salesAchievement'])->name('spv.sales-achievement');
        Route::get('/spv/sales-visits', [MobileDashboardController::class, 'salesVisits'])->name('spv.sales-visits');

        // SPV Sales Pembelian Approvals
        Route::get('/spv/pembelian-pending', [MobileDashboardController::class, 'pendingPembelianListSpv'])->name('spv.pembelian.pending');
        Route::post('/spv/approve-pembelian/{no_faktur}', [MobileDashboardController::class, 'approvePembelianSpv'])->name('spv.pembelian.approve');
    });

    // Authenticated Mobile Routes for Owner
    Route::prefix('m/owner')->name('mobile.owner.')->middleware('owner.mobile')->group(function () {
        Route::get('/', [MobileOwnerController::class, 'index'])->name('dashboard');
        Route::get('/low-stock', [MobileOwnerController::class, 'lowStock'])->name('low-stock');
        Route::get('/pending-approval', [MobileOwnerController::class, 'pendingApproval'])->name('pending-approval');
        Route::post('/approve-limit/{id}', [MobileOwnerController::class, 'approveLimit'])->name('approve-limit');
        Route::post('/reject-limit/{id}', [MobileOwnerController::class, 'rejectLimit'])->name('reject-limit');
        Route::get('/laba-rugi', [MobileOwnerController::class, 'labaRugi'])->name('laba-rugi');
        Route::get('/sales-achievement', [MobileOwnerController::class, 'salesAchievement'])->name('sales-achievement');
        Route::get('/sales-visits', [MobileOwnerController::class, 'salesVisits'])->name('sales-visits');
        Route::get('/order', [MobileOwnerController::class, 'orders'])->name('order.index');

        // Customer approvals
        Route::get('/pending-pelanggan', [MobileOwnerController::class, 'pendingPelanggan'])->name('pending-pelanggan');
        Route::post('/approve-pelanggan/{kode_pelanggan}', [MobileOwnerController::class, 'approvePelanggan'])->name('approve-pelanggan');
        Route::post('/reject-pelanggan/{kode_pelanggan}', [MobileOwnerController::class, 'rejectPelanggan'])->name('reject-pelanggan');
    });

    // Shared Search / API Routes (Accessible by both Admin & Sales)
    Route::get('/barang-search', [BarangController::class, 'search'])->name('barang.search');
    Route::get('/pelanggan-search', [PelangganController::class, 'search'])->name('pelanggan.search');
    Route::get('/penjualan-by-pelanggan', [PenjualanController::class, 'getByPelanggan'])->name('penjualan.by-pelanggan');
    Route::get('/penjualan/history-barang', [PenjualanController::class, 'historyBarang'])->name('penjualan.history-barang');

    // Profile Settings (Accessible by any desktop authenticated user)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/set-target', [DashboardController::class, 'setTarget'])->name('dashboard.set-target');

        Route::resource('kategori', KategoriController::class);
        Route::resource('merk', MerkController::class);
        Route::resource('supplier', SupplierController::class);
        Route::get('/barang/update-harga-masal', [BarangController::class, 'editHargaMasal'])->name('barang.edit-harga-masal');
        Route::post('/barang/update-harga-masal', [BarangController::class, 'updateHargaMasal'])->name('barang.update-harga-masal');
        Route::resource('barang', BarangController::class);
        Route::resource('barang_satuan', BarangSatuanController::class);
        Route::resource('pelanggan', PelangganController::class);
        Route::get('/pelanggan-map', [PelangganController::class, 'map'])->name('pelanggan.map');
        Route::post('/pelanggan/{id}/toggle-status', [PelangganController::class, 'toggleStatus'])->name('pelanggan.toggle-status');
        Route::post('/pelanggan/{id}/toggle-jenis', [PelangganController::class, 'toggleJenis'])->name('pelanggan.toggle-jenis');
        Route::post('/pelanggan/{kode_pelanggan}/approve', [PelangganController::class, 'approve'])->name('pelanggan.approve');
        Route::post('/pelanggan/{kode_pelanggan}/reject', [PelangganController::class, 'reject'])->name('pelanggan.reject');
        Route::get('/pelanggan/{kode_pelanggan}/sisa-limit', [PelangganController::class, 'sisaLimitDetail'])->name('pelanggan.sisa-limit');

        Route::resource('users', UserController::class);
        Route::resource('diskon-strata', DiskonStrataController::class);
        Route::post('/diskon-strata/{id}/toggle-status', [DiskonStrataController::class, 'toggleStatus'])->name('diskon-strata.toggle-status');

        Route::post('/penjualan/{no_faktur}/payment', [PenjualanController::class, 'storePayment'])->name('penjualan.payment');
        Route::post('/penjualan/{no_faktur}/batal', [PenjualanController::class, 'batal'])->name('penjualan.batal');
        Route::post('/penjualan/{no_faktur}/restore', [PenjualanController::class, 'restore'])->name('penjualan.restore');
        Route::get('/penjualan/{no_faktur}/print', [PenjualanController::class, 'print'])->name('penjualan.print');
        Route::get('/retur-penjualan/{no_retur}/print', [ReturPenjualanController::class, 'print'])->name('retur-penjualan.print');
        Route::get('/penjualan/{penjualan}/edit', [PenjualanController::class, 'edit'])->name('penjualan.edit');
        Route::get('/retur-penjualan/{retur_penjualan}/edit', [ReturPenjualanController::class, 'edit'])->name('retur-penjualan.edit');

        Route::resource('penjualan', PenjualanController::class);
        Route::resource('retur-penjualan', ReturPenjualanController::class);
        Route::get('/canvas/{canvas}/print', [CanvasController::class, 'print'])->name('canvas.print');
        Route::resource('canvas', CanvasController::class);

        // Kiriman Penjualan Routes
        Route::get('/penjualan-kiriman', [PenjualanKirimanController::class, 'index'])->name('penjualan-kiriman.index');
        Route::get('/penjualan-kiriman/create', [PenjualanKirimanController::class, 'create'])->name('penjualan-kiriman.create');
        Route::post('/penjualan-kiriman', [PenjualanKirimanController::class, 'store'])->name('penjualan-kiriman.store');
        Route::get('/penjualan-kiriman/edit', [PenjualanKirimanController::class, 'edit'])->name('penjualan-kiriman.edit');
        Route::post('/penjualan-kiriman/update', [PenjualanKirimanController::class, 'update'])->name('penjualan-kiriman.update');
        Route::delete('/penjualan-kiriman/delete', [PenjualanKirimanController::class, 'destroy'])->name('penjualan-kiriman.destroy');
        Route::get('/penjualan-kiriman/cetak-rekap', [PenjualanKirimanController::class, 'cetakRekap'])->name('penjualan-kiriman.cetak-rekap');
        Route::get('/penjualan-kiriman/cetak-barang', [PenjualanKirimanController::class, 'cetakBarang'])->name('penjualan-kiriman.cetak-barang');
        Route::get('/penjualan-kiriman/get-invoices', [PenjualanKirimanController::class, 'getInvoices'])->name('penjualan-kiriman.get-invoices');

        Route::get('/activity-logs/{no_faktur}', [ActivityLogController::class, 'getLogs'])->name('activity-logs.get');

        Route::get('/pembelian/{no_faktur}/items', [PembelianController::class, 'getPurchaseItems'])->name('pembelian.items');
        Route::post('/pembelian/{id}/payment', [PembelianController::class, 'storePayment'])->name('pembelian.payment');
        Route::post('/pembelian/{id}/approve', [PembelianController::class, 'approve'])->name('pembelian.approve');
        Route::get('/pembelian/{pembelian}/edit', [PembelianController::class, 'edit'])->name('pembelian.edit');
        Route::resource('pembelian', PembelianController::class);
        Route::resource('retur-pembelian', ReturPembelianController::class);
        Route::resource('stok-opname', StokOpnameController::class);
        Route::resource('kas-bank', KeuanganMutasiController::class);

        // Ajuan Limit Kredit
        Route::resource('ajuan-limit-kredit', AjuanLimitKreditController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/ajuan-limit-kredit/{id}/approve', [AjuanLimitKreditController::class, 'approve'])->name('ajuan-limit-kredit.approve');
        Route::post('/ajuan-limit-kredit/{id}/reject', [AjuanLimitKreditController::class, 'reject'])->name('ajuan-limit-kredit.reject');

        // Sales GPS Tracking
        Route::get('/sales-tracking', [SalesTrackingController::class, 'index'])->name('sales-tracking.index');

        // Laporan Routes
        Route::get('/laporan/pembelian', [LaporanPembelianController::class, 'laporanPembelian'])->name('laporan.pembelian');
        Route::get('/laporan/pembelian/cetak', [LaporanPembelianController::class, 'laporanPembelian'])->name('laporan.pembelian.cetak');
        Route::get('/laporan/pembelian/excel', [LaporanPembelianController::class, 'laporanPembelian'])->name('laporan.pembelian.excel');
        Route::get('/laporan/retur-pembelian', [LaporanPembelianController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian');
        Route::get('/laporan/retur-pembelian/cetak', [LaporanPembelianController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian.cetak');
        Route::get('/laporan/retur-pembelian/excel', [LaporanPembelianController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian.excel');
        
        Route::get('/laporan/stok', [LaporanStokController::class, 'laporanStok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [LaporanStokController::class, 'laporanStok'])->name('laporan.stok.cetak');
        Route::get('/laporan/stok/excel', [LaporanStokController::class, 'laporanStok'])->name('laporan.stok.excel');
        
        Route::get('/laporan/penjualan', [LaporanPenjualanController::class, 'laporanPenjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [LaporanPenjualanController::class, 'laporanPenjualan'])->name('laporan.penjualan.cetak');
        Route::get('/laporan/penjualan/excel', [LaporanPenjualanController::class, 'laporanPenjualan'])->name('laporan.penjualan.excel');
        Route::get('/laporan/retur-penjualan', [LaporanPenjualanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan');
        Route::get('/laporan/retur-penjualan/cetak', [LaporanPenjualanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan.cetak');
        Route::get('/laporan/retur-penjualan/excel', [LaporanPenjualanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan.excel');
        
        Route::get('/laporan/piutang', [LaporanKeuanganController::class, 'laporanPiutang'])->name('laporan.piutang');
        Route::get('/laporan/piutang/cetak', [LaporanKeuanganController::class, 'laporanPiutang'])->name('laporan.piutang.cetak');
        Route::get('/laporan/piutang/excel', [LaporanKeuanganController::class, 'laporanPiutang'])->name('laporan.piutang.excel');
        Route::get('/laporan/rekap-sisa-piutang', [LaporanKeuanganController::class, 'laporanRekapSisaPiutang'])->name('laporan.rekap-sisa-piutang');
        Route::get('/laporan/rekap-sisa-piutang/cetak', [LaporanKeuanganController::class, 'laporanRekapSisaPiutang'])->name('laporan.rekap-sisa-piutang.cetak');
        Route::get('/laporan/rekap-sisa-piutang/excel', [LaporanKeuanganController::class, 'laporanRekapSisaPiutang'])->name('laporan.rekap-sisa-piutang.excel');
        Route::get('/laporan/pembayaran-piutang', [LaporanKeuanganController::class, 'laporanPembayaranPiutang'])->name('laporan.pembayaran_piutang');
        Route::get('/laporan/pembayaran-piutang/cetak', [LaporanKeuanganController::class, 'laporanPembayaranPiutang'])->name('laporan.pembayaran_piutang.cetak');
        Route::get('/laporan/pembayaran-piutang/excel', [LaporanKeuanganController::class, 'laporanPembayaranPiutang'])->name('laporan.pembayaran_piutang.excel');
        Route::get('/laporan/setoran', [LaporanKeuanganController::class, 'laporanSetoran'])->name('laporan.setoran');
        Route::get('/laporan/setoran/cetak', [LaporanKeuanganController::class, 'laporanSetoran'])->name('laporan.setoran.cetak');
        Route::get('/laporan/setoran/excel', [LaporanKeuanganController::class, 'laporanSetoran'])->name('laporan.setoran.excel');
        
        Route::get('/laporan/laba-rugi', [LaporanKeuanganController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi');
        Route::post('/laporan/laba-rugi/cetak', [LaporanKeuanganController::class, 'cetakLabaRugi'])->name('cetakLabaRugi');
        Route::get('/laporan/laba-rugi/cetak', [LaporanKeuanganController::class, 'cetakLabaRugi'])->name('laporan.laba-rugi.cetak');
        Route::get('/laporan/laba-rugi/excel', [LaporanKeuanganController::class, 'cetakLabaRugi'])->name('laporan.laba-rugi.excel');

        Route::get('/laporan/kas-bank', [LaporanKeuanganController::class, 'laporanKasBank'])->name('laporan.kas-bank');
        Route::get('/laporan/kas-bank/cetak', [LaporanKeuanganController::class, 'laporanKasBank'])->name('laporan.kas-bank.cetak');
        Route::get('/laporan/kas-bank/excel', [LaporanKeuanganController::class, 'laporanKasBank'])->name('laporan.kas-bank.excel');

        // Roles & Permissions
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('roles.store');
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroyRole'])->name('roles.destroy');
        Route::post('/roles/permissions', [RolePermissionController::class, 'updatePermissions'])->name('roles.permissions.update');

        Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');

        // Payment Approvals
        Route::get('/pembayaran/pending', [PenjualanController::class, 'pendingPayments'])->name('pembayaran.pending.index');
        Route::post('/pembayaran/{id}/approve', [PenjualanController::class, 'approvePayment'])->name('pembayaran.approve');
        Route::post('/pembayaran/{id}/reject', [PenjualanController::class, 'rejectPayment'])->name('pembayaran.reject');
        Route::post('/pembayaran/{id}/cancel-approval', [PenjualanController::class, 'cancelPaymentApproval'])->name('pembayaran.cancel-approval');
        Route::post('/pembayaran/{id}/edit', [PenjualanController::class, 'updatePayment'])->name('pembayaran.update-payment');
        Route::delete('/pembayaran/{id}', [PenjualanController::class, 'destroyPayment'])->name('pembayaran.destroy');
    });
});
