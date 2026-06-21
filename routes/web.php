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
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\KeuanganMutasiController;

// Mobile Controllers
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileKunjunganController;
use App\Http\Controllers\Mobile\MobileOrderController;
use App\Http\Controllers\Mobile\MobileAjuanLimitController;
use App\Http\Controllers\Mobile\MobileOwnerController;
use App\Http\Controllers\Mobile\MobilePelangganController;

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

        // Kunjungan (Visits)
        Route::get('/kunjungan', [MobileKunjunganController::class, 'index'])->name('kunjungan.index');
        Route::post('/kunjungan/checkin', [MobileKunjunganController::class, 'checkin'])->name('kunjungan.checkin');
        Route::post('/kunjungan/checkout', [MobileKunjunganController::class, 'checkout'])->name('kunjungan.checkout');

        // Orders
        Route::get('/order', [MobileOrderController::class, 'index'])->name('order.index');
        Route::get('/order/create', [MobileOrderController::class, 'create'])->name('order.create');
        Route::post('/order/store', [MobileOrderController::class, 'store'])->name('order.store');
        Route::post('/order/{no_faktur}/payment', [MobileOrderController::class, 'storePayment'])->name('order.payment');

        // Ajuan Limit Kredit
        Route::get('/limit-kredit', [MobileAjuanLimitController::class, 'index'])->name('limit-kredit.index');
        Route::get('/limit-kredit/create', [MobileAjuanLimitController::class, 'create'])->name('limit-kredit.create');
        Route::post('/limit-kredit/store', [MobileAjuanLimitController::class, 'store'])->name('limit-kredit.store');

        // Pelanggan (Customers)
        Route::get('/pelanggan/create', [MobilePelangganController::class, 'create'])->name('pelanggan.create');
        Route::post('/pelanggan/store', [MobilePelangganController::class, 'store'])->name('pelanggan.store');
        
        // SPV Sales Customer Approvals
        Route::get('/spv/pelanggan-pending', [MobilePelangganController::class, 'pendingListSpv'])->name('spv.pelanggan.pending');
        Route::post('/spv/approve-pelanggan/{kode_pelanggan}', [MobilePelangganController::class, 'approveSpv'])->name('spv.pelanggan.approve');
        Route::post('/spv/reject-pelanggan/{kode_pelanggan}', [MobilePelangganController::class, 'rejectSpv'])->name('spv.pelanggan.reject');
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

        // Customer approvals
        Route::get('/pending-pelanggan', [MobileOwnerController::class, 'pendingPelanggan'])->name('pending-pelanggan');
        Route::post('/approve-pelanggan/{kode_pelanggan}', [MobileOwnerController::class, 'approvePelanggan'])->name('approve-pelanggan');
        Route::post('/reject-pelanggan/{kode_pelanggan}', [MobileOwnerController::class, 'rejectPelanggan'])->name('reject-pelanggan');
    });

    // Shared Search / API Routes (Accessible by both Admin & Sales)
    Route::get('/barang-search', [BarangController::class, 'search'])->name('barang.search');
    Route::get('/pelanggan-search', [PelangganController::class, 'search'])->name('pelanggan.search');

    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('kategori', KategoriController::class);
        Route::resource('merk', MerkController::class);
        Route::resource('supplier', SupplierController::class);
        Route::resource('barang', BarangController::class);
        Route::resource('barang_satuan', BarangSatuanController::class);
        Route::resource('pelanggan', PelangganController::class);
        Route::post('/pelanggan/{id}/toggle-status', [PelangganController::class, 'toggleStatus'])->name('pelanggan.toggle-status');
        Route::post('/pelanggan/{kode_pelanggan}/approve', [PelangganController::class, 'approve'])->name('pelanggan.approve');
        Route::post('/pelanggan/{kode_pelanggan}/reject', [PelangganController::class, 'reject'])->name('pelanggan.reject');
        Route::resource('users', UserController::class);
        Route::resource('diskon-strata', DiskonStrataController::class);
        Route::post('/diskon-strata/{id}/toggle-status', [DiskonStrataController::class, 'toggleStatus'])->name('diskon-strata.toggle-status');

        Route::post('/penjualan/{no_faktur}/payment', [PenjualanController::class, 'storePayment'])->name('penjualan.payment');
        Route::post('/penjualan/{no_faktur}/batal', [PenjualanController::class, 'batal'])->name('penjualan.batal');
        Route::get('/penjualan/{no_faktur}/print', [PenjualanController::class, 'print'])->name('penjualan.print');
        Route::get('/retur-penjualan/{no_retur}/print', [ReturPenjualanController::class, 'print'])->name('retur-penjualan.print');
        Route::resource('penjualan', PenjualanController::class);
        Route::resource('retur-penjualan', ReturPenjualanController::class);

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
        Route::get('/laporan/pembelian', [LaporanController::class, 'laporanPembelian'])->name('laporan.pembelian');
        Route::get('/laporan/pembelian/cetak', [LaporanController::class, 'laporanPembelian'])->name('laporan.pembelian.cetak');
        Route::get('/laporan/pembelian/excel', [LaporanController::class, 'laporanPembelian'])->name('laporan.pembelian.excel');
        Route::get('/laporan/retur-pembelian', [LaporanController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian');
        Route::get('/laporan/retur-pembelian/cetak', [LaporanController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian.cetak');
        Route::get('/laporan/retur-pembelian/excel', [LaporanController::class, 'laporanReturPembelian'])->name('laporan.retur-pembelian.excel');
        Route::get('/laporan/stok', [LaporanController::class, 'laporanStok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [LaporanController::class, 'laporanStok'])->name('laporan.stok.cetak');
        Route::get('/laporan/stok/excel', [LaporanController::class, 'laporanStok'])->name('laporan.stok.excel');
        Route::get('/laporan/penjualan', [LaporanController::class, 'laporanPenjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [LaporanController::class, 'laporanPenjualan'])->name('laporan.penjualan.cetak');
        Route::get('/laporan/penjualan/excel', [LaporanController::class, 'laporanPenjualan'])->name('laporan.penjualan.excel');
        Route::get('/laporan/retur-penjualan', [LaporanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan');
        Route::get('/laporan/retur-penjualan/cetak', [LaporanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan.cetak');
        Route::get('/laporan/retur-penjualan/excel', [LaporanController::class, 'laporanReturPenjualan'])->name('laporan.retur-penjualan.excel');
        Route::get('/laporan/piutang', [LaporanController::class, 'laporanPiutang'])->name('laporan.piutang');
        Route::get('/laporan/piutang/cetak', [LaporanController::class, 'laporanPiutang'])->name('laporan.piutang.cetak');
        Route::get('/laporan/piutang/excel', [LaporanController::class, 'laporanPiutang'])->name('laporan.piutang.excel');
        Route::get('/laporan/setoran', [LaporanController::class, 'laporanSetoran'])->name('laporan.setoran');
        Route::get('/laporan/setoran/cetak', [LaporanController::class, 'laporanSetoran'])->name('laporan.setoran.cetak');
        Route::get('/laporan/setoran/excel', [LaporanController::class, 'laporanSetoran'])->name('laporan.setoran.excel');

        Route::get('/laporan/laba-rugi', [LaporanController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/laba-rugi/cetak', [LaporanController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi.cetak');
        Route::get('/laporan/laba-rugi/excel', [LaporanController::class, 'laporanLabaRugi'])->name('laporan.laba-rugi.excel');

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
    });
});
