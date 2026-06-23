<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Penjualan;
use App\Models\PenjualanCheckin;
use App\Models\Pelanggan;
use App\Models\Barang;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MobileOwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the mobile owner dashboard page loads and includes top sales achievements.
     */
    public function test_mobile_owner_dashboard_shows_top_sales_achievements(): void
    {
        // Setup Roles
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $salesRole = Role::firstOrCreate(['name' => 'Sales']);

        // Setup Owner
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'nik' => 'OWN01',
            'status' => '1',
            'role' => 'owner'
        ]);
        $owner->assignRole($ownerRole);

        // Setup Salespersons
        $sales1 = User::create([
            'name' => 'Alice Sales',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS01',
            'status' => '1',
            'role' => 'sales'
        ]);
        $sales1->assignRole($salesRole);

        $sales2 = User::create([
            'name' => 'Bob Sales',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS02',
            'status' => '1',
            'role' => 'sales'
        ]);
        $sales2->assignRole($salesRole);

        // Setup Customer
        $pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'CUST01',
            'nama_pelanggan' => 'Toko Maju',
            'alamat_pelanggan' => 'Jl. Merdeka 10',
            'status' => 1,
            'approve' => 1
        ]);

        // Setup Penjualan (Sales) for Alice
        Penjualan::create([
            'no_faktur' => 'FACT-001',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => 'CUST01',
            'kode_sales' => 'SLS01',
            'grand_total' => 1500000,
            'jenis_transaksi' => 'Tunai',
            'batal' => 0,
            'user_id' => $owner->id
        ]);

        // Setup Penjualan (Sales) for Bob
        Penjualan::create([
            'no_faktur' => 'FACT-002',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => 'CUST01',
            'kode_sales' => 'SLS02',
            'grand_total' => 800000,
            'jenis_transaksi' => 'Kredit',
            'batal' => 0,
            'user_id' => $owner->id
        ]);

        // Setup Check-in/Visits
        PenjualanCheckin::create([
            'kode_sales' => 'SLS01',
            'kode_pelanggan' => 'CUST01',
            'tanggal' => date('Y-m-d'),
            'checkin' => date('Y-m-d H:i:s'),
            'checkout' => date('Y-m-d H:i:s')
        ]);

        // Access Mobile Owner Dashboard
        $response = $this->actingAs($owner)->get(route('mobile.owner.dashboard'));

        $response->assertStatus(200);

        // Verify top sales data is passed to the view
        $response->assertViewHas('topSales');
        $topSales = $response->viewData('topSales');

        $this->assertCount(2, $topSales);
        
        // Alice should be first (sales: 1.5M), Bob second (sales: 800k)
        $this->assertEquals('Alice Sales', $topSales[0]['name']);
        $this->assertEquals(1500000, $topSales[0]['total_sales']);
        $this->assertEquals(1, $topSales[0]['visit_count']);

        $this->assertEquals('Bob Sales', $topSales[1]['name']);
        $this->assertEquals(800000, $topSales[1]['total_sales']);

        // Check HTML renders leaderboard content
        $response->assertSee('Top Pencapaian Sales');
        $response->assertSee('Alice Sales');
        $response->assertSee('Bob Sales');
        $response->assertSee('Rp 1.500.000');
        $response->assertSee('Rp 800.000');
    }
}
