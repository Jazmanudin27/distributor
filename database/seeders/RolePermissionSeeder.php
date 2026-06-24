<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan daftar menu dan action (Permission)
        $menus = [
            // Data Master
            'kategori' => ['view', 'create', 'edit', 'delete'],
            'merk' => ['view', 'create', 'edit', 'delete'],
            'supplier' => ['view', 'create', 'edit', 'delete'],
            'barang' => ['view', 'create', 'edit', 'delete'],
            'barang_satuan' => ['view', 'create', 'edit', 'delete'],
            'pelanggan' => ['view', 'create', 'edit', 'delete'],
            'wilayah' => ['view', 'create', 'edit', 'delete'],
            'sub_wilayah' => ['view', 'create', 'edit', 'delete'],
            'diskon_strata' => ['view', 'create', 'edit', 'delete'],

            // Transaksi Pembelian
            'purchase_orders' => ['view', 'create', 'edit', 'delete', 'approve'],
            'pembelian' => ['view', 'create', 'edit', 'delete', 'approve'],
            'retur_pembelian' => ['view', 'create', 'edit', 'delete'],
            'stok_opname' => ['view', 'create', 'edit', 'delete'],

            // Transaksi Penjualan
            'penjualan' => ['view', 'create', 'edit', 'delete'],
            'retur_penjualan' => ['view', 'create', 'edit', 'delete'],
            'penjualan_kiriman' => ['view', 'create', 'edit', 'delete'],
            'setoran_penjualan' => ['view', 'create', 'edit', 'delete'],

            // Inventory / Mutasi
            'mutasi_barang_masuk' => ['view', 'create', 'edit', 'delete'],
            'mutasi_barang_keluar' => ['view', 'create', 'edit', 'delete'],

            // Keuangan & Akuntansi
            'keuangan_mutasi' => ['view', 'create', 'edit', 'delete'],
            'kas_kecil' => ['view', 'create', 'edit', 'delete'],
            'bank' => ['view', 'create', 'edit', 'delete'],
            'coa' => ['view', 'create', 'edit', 'delete'],
            'ledger' => ['view', 'create', 'edit', 'delete'],
            'tutup_laporan' => ['view', 'create', 'edit', 'delete'],

            // HRD
            'hrd_karyawan' => ['view', 'create', 'edit', 'delete'],
            'departemen' => ['view', 'create', 'edit', 'delete'],

            // Pengajuan / Approval
            'pengajuan_limit_kredit' => ['view', 'create', 'edit', 'delete', 'approve'],
            'pengajuan_limit_faktur' => ['view', 'create', 'edit', 'delete', 'approve'],
            'pengajuan_limit_supplier' => ['view', 'create', 'edit', 'delete', 'approve'],

            // Laporan
            'laporan_pembelian' => ['view'],
            'laporan_retur_pembelian' => ['view'],
            'laporan_stok' => ['view'],
            'laporan_penjualan' => ['view'],
            'laporan_retur_penjualan' => ['view'],
            'laporan_piutang' => ['view'],
            'laporan_setoran' => ['view'],
            'laporan_laba_rugi' => ['view'],
            'laporan_kas_bank' => ['view'],


            // Settings
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ];

        // 2. Buat permissions
        foreach ($menus as $menu => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $action . '-' . $menu]);
            }
        }

        // 3. Buat Role Super Admin dan assign semua permission
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());

        // 4. Buat Role contoh lain (Admin, Kasir, dsb) jika belum ada
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $kasir = Role::firstOrCreate(['name' => 'Kasir']);
        $salesman = Role::firstOrCreate(['name' => 'Salesman']);
        $spvSales = Role::firstOrCreate(['name' => 'SPV Sales']);
        $owner = Role::firstOrCreate(['name' => 'Owner']);

        // Owner gets all permissions (business owner)
        $owner->syncPermissions(Permission::all());

        // Berikan beberapa akses default ke Admin (kecuali hapus users/roles)
        $adminPermissions = Permission::whereNotIn('name', [
            'delete-users',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles'
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Berikan akses default ke Kasir (hanya view barang, pelanggan, dll)
        $kasirPermissions = Permission::whereIn('name', [
            'view-barang',
            'view-kategori',
            'view-merk',
            'view-pelanggan'
        ])->get();
        $kasir->syncPermissions($kasirPermissions);

        // Berikan akses default ke Salesman
        $salesmanPermissions = Permission::whereIn('name', [
            'view-barang',
            'view-kategori',
            'view-merk',
            'view-pelanggan',
            'view-penjualan',
            'create-penjualan',
            'view-pengajuan_limit_kredit',
            'create-pengajuan_limit_kredit'
        ])->get();
        $salesman->syncPermissions($salesmanPermissions);

        // Berikan akses default ke SPV Sales
        $spvSalesPermissions = Permission::whereIn('name', [
            'view-barang',
            'view-kategori',
            'view-merk',
            'view-pelanggan',
            'edit-pelanggan',
            'view-ajuan_limit_kredit',
            'approve-ajuan_limit_kredit',
            'view-penjualan',
            'view-laporan_penjualan',
            'view-laporan_piutang',
            'view-pembelian',
            'approve-pembelian',
        ])->get();
        $spvSales->syncPermissions($spvSalesPermissions);

        // 5. Assign Super Admin role to the first user
        $user = \App\Models\User::first();
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}
