<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpvSalesFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin role and assign necessary permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permissions = [
            'view-penjualan',
            'create-penjualan',
            'view-laporan_penjualan',
            'view-laporan_piutang',
            'view-laporan_setoran',
        ];

        foreach ($permissions as $p) {
            $perm = Permission::firstOrCreate(['name' => $p]);
            $adminRole->givePermissionTo($perm);
        }

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@distributor.com',
            'password' => bcrypt('password'),
            'nik' => 'ADM001',
            'status' => '1',
            'role' => 'admin',
            'is_kanvas' => 0,
        ]);
        $this->admin->assignRole($adminRole);

        // Create regular Sales (non-canvas)
        $this->regularSales = User::create([
            'name' => 'Sales Budi',
            'email' => 'budi_sales@distributor.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS001',
            'status' => '1',
            'role' => 'sales',
            'is_kanvas' => 0,
        ]);

        // Create SPV Sales (non-canvas)
        $this->spvSales = User::create([
            'name' => 'SPV Joko',
            'email' => 'joko_spv@distributor.com',
            'password' => bcrypt('password'),
            'nik' => 'SPV001',
            'status' => '1',
            'role' => 'spv sales',
            'is_kanvas' => 0,
        ]);

        // Create Canvas Sales
        $this->canvasSales = User::create([
            'name' => 'Sales Anto Canvas',
            'email' => 'anto_canvas@distributor.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS002',
            'status' => '1',
            'role' => 'sales',
            'is_kanvas' => 1,
        ]);
    }

    public function test_scope_salesmen_includes_both_sales_and_spv_sales(): void
    {
        $salesmen = User::salesmen()->get();
        $niks = $salesmen->pluck('nik')->toArray();

        $this->assertContains('SLS001', $niks);
        $this->assertContains('SPV001', $niks);
        $this->assertContains('SLS002', $niks);
        $this->assertNotContains('ADM001', $niks);
    }

    public function test_spv_sales_appears_in_penjualan_index_filter_as_non_canvas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('penjualan.index', [
            'kategori_sales' => 'non_canvas',
        ]));

        $response->assertStatus(200);
        $response->assertSee('SPV Joko');
        $response->assertSee('Sales Budi');
        $response->assertDontSee('Sales Anto Canvas');
    }

    public function test_spv_sales_does_not_appear_in_penjualan_index_canvas_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('penjualan.index', [
            'kategori_sales' => 'canvas',
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('SPV Joko');
        $response->assertDontSee('Sales Budi');
        $response->assertSee('Sales Anto Canvas');
    }

    public function test_spv_sales_appears_in_rekap_tagihan_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.rekap-sisa-piutang', [
            'kategori_sales' => 'non_canvas',
        ]));

        $response->assertStatus(200);
        $response->assertSee('SPV Joko');
        $response->assertSee('Sales Budi');
    }

    public function test_spv_sales_appears_in_laporan_penjualan_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('laporan.penjualan', [
            'kategori_sales' => 'non_canvas',
        ]));

        $response->assertStatus(200);
        $response->assertSee('SPV Joko');
        $response->assertSee('Sales Budi');
    }

    public function test_spv_sales_appears_in_penjualan_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('penjualan.create'));

        $response->assertStatus(200);
        $response->assertSee('SPV Joko');
        $response->assertSee('Sales Budi');
    }
}
