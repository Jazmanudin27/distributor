<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LaporanRenderingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all redesigned report pages can be rendered for authorized admin users.
     */
    public function test_all_reports_render_successfully(): void
    {
        // Create Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        
        // Define all report permissions
        $permissions = [
            'view-laporan_pembelian',
            'view-laporan_retur_pembelian',
            'view-laporan_stok',
            'view-laporan_penjualan',
            'view-laporan_retur_penjualan',
            'view-laporan_piutang',
            'view-laporan_laba_rugi',
            'view-laporan_setoran',
        ];

        foreach ($permissions as $p) {
            $permission = Permission::firstOrCreate(['name' => $p]);
            $adminRole->givePermissionTo($permission);
        }

        // Create an admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@distributor.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'nik' => '001',
                'status' => '1',
                'role' => 'admin'
            ]
        );
        $user->assignRole($adminRole);

        // Define all routes to test
        $reportRoutes = [
            // 1. Pembelian
            ['route' => 'laporan.pembelian', 'params' => []],
            ['route' => 'laporan.pembelian.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.pembelian.excel', 'params' => ['jenis_laporan' => 'rekap']],

            // 2. Retur Pembelian
            ['route' => 'laporan.retur-pembelian', 'params' => []],
            ['route' => 'laporan.retur-pembelian.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.retur-pembelian.excel', 'params' => ['jenis_laporan' => 'rekap']],

            // 3. Stok
            ['route' => 'laporan.stok', 'params' => []],
            ['route' => 'laporan.stok.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.stok.excel', 'params' => ['jenis_laporan' => 'rekap']],

            // 4. Penjualan (Testing Format 1, 2, and 3)
            ['route' => 'laporan.penjualan', 'params' => []],
            ['route' => 'laporan.penjualan.cetak', 'params' => ['jenis_laporan' => 'rekap']], // Format 1
            ['route' => 'laporan.penjualan.excel', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.penjualan.cetak', 'params' => ['jenis_laporan' => 'detail']], // Format 2
            ['route' => 'laporan.penjualan.excel', 'params' => ['jenis_laporan' => 'detail']],
            ['route' => 'laporan.penjualan.cetak', 'params' => ['jenis_laporan' => 'detail_simple']], // Format 3
            ['route' => 'laporan.penjualan.excel', 'params' => ['jenis_laporan' => 'detail_simple']],

            // 5. Retur Penjualan
            ['route' => 'laporan.retur-penjualan', 'params' => []],
            ['route' => 'laporan.retur-penjualan.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.retur-penjualan.excel', 'params' => ['jenis_laporan' => 'rekap']],

            // 6. Piutang
            ['route' => 'laporan.piutang', 'params' => []],
            ['route' => 'laporan.piutang.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.piutang.excel', 'params' => ['jenis_laporan' => 'rekap']],

            // 7. Rekap Sisa Piutang
            ['route' => 'laporan.rekap-sisa-piutang', 'params' => []],
            ['route' => 'laporan.rekap-sisa-piutang.cetak', 'params' => []],
            ['route' => 'laporan.rekap-sisa-piutang.excel', 'params' => []],

            // 8. Pembayaran Piutang
            ['route' => 'laporan.pembayaran_piutang', 'params' => []],
            ['route' => 'laporan.pembayaran_piutang.cetak', 'params' => []],
            ['route' => 'laporan.pembayaran_piutang.excel', 'params' => []],

            // 9. Setoran
            ['route' => 'laporan.setoran', 'params' => []],
            ['route' => 'laporan.setoran.cetak', 'params' => []],
            ['route' => 'laporan.setoran.excel', 'params' => []],

            // 10. Laba Rugi
            ['route' => 'laporan.laba-rugi', 'params' => []],
            ['route' => 'laporan.laba-rugi.cetak', 'params' => ['jenis_laporan' => 'rekap']],
            ['route' => 'laporan.laba-rugi.excel', 'params' => ['jenis_laporan' => 'rekap']],
        ];

        foreach ($reportRoutes as $routeData) {
            $response = $this->actingAs($user)->get(route($routeData['route'], $routeData['params']));
            $response->assertStatus(200);
        }
    }
}
