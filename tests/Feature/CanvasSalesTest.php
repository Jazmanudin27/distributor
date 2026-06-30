<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\CanvasSession;
use App\Models\CanvasSessionDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pelanggan;
use App\Models\StokMutasi;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CanvasSalesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $salesman;
    private Barang $barang;
    private BarangSatuan $satuan;
    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup roles and permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        Permission::firstOrCreate(['name' => 'view-users']);
        Permission::firstOrCreate(['name' => 'view-penjualan']);
        Permission::firstOrCreate(['name' => 'view-canvas']);
        $adminRole->givePermissionTo(['view-users', 'view-penjualan', 'view-canvas']);

        $salesRole = Role::firstOrCreate(['name' => 'sales']);

        // 2. Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'nik' => 'ADM-001',
            'role' => 'admin',
            'status' => '1',
            'is_kanvas' => false
        ]);
        $this->admin->assignRole($adminRole);

        // 3. Create canvas salesman user
        $this->salesman = User::create([
            'name' => 'Sales Kanvas 1',
            'email' => 'sales1@test.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS-001',
            'role' => 'sales',
            'status' => '1',
            'is_kanvas' => true
        ]);
        $this->salesman->assignRole($salesRole);

        // 4. Create product and unit
        $this->barang = Barang::create([
            'kode_barang' => 'BRG-TST',
            'nama_barang' => 'Barang Test',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 50.00,
            'status' => 1
        ]);

        $this->satuan = BarangSatuan::create([
            'kode_barang' => $this->barang->kode_barang,
            'satuan' => 'PCS',
            'isi' => 1,
            'harga_pokok' => 1000,
            'harga_jual' => 1500
        ]);

        // 5. Create customer
        $this->pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'PLG-001',
            'nama_pelanggan' => 'Pelanggan Test',
            'status' => 1,
            'limit_kredit' => 1000000,
            'metode_bayar' => 'Cash',
            'kode_sales' => $this->salesman->nik
        ]);
    }

    /**
     * Test starting a canvas session (loading goods) reduces warehouse stock.
     */
    public function test_loading_canvas_reduces_warehouse_stock(): void
    {
        $response = $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Loading pagi hari',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Get canvas session and approve it
        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        // Stock in warehouse should be reduced by 10 (from 50 to 40)
        $this->barang->refresh();
        $this->assertEquals(40.00, (float)$this->barang->stok);

        // Canvas session should be recorded in loading status
        $session->refresh();
        $this->assertEquals('loading', $session->status);

        // Stock mutation log should exist
        $this->assertDatabaseHas('stok_mutasi', [
            'kode_barang' => $this->barang->kode_barang,
            'jenis_transaksi' => 'Canvas Ambil',
            'qty_keluar' => 10.00
        ]);
    }

    /**
     * Test canvas sale does not reduce warehouse stock again and increments session sold qty.
     */
    public function test_canvas_sale_bypasses_warehouse_deduction_and_tracks_sold_qty(): void
    {
        // 1. First, load 10 units onto canvas
        $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        $this->barang->refresh();
        $this->assertEquals(40.00, (float)$this->barang->stok);

        // 2. Create sales invoice by canvas salesman (selling 7 units)
        $response = $this->actingAs($this->admin)->post(route('penjualan.store'), [
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'diskon_global' => 0,
            'jenis_transaksi' => 'Tunai',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'satuan' => 'PCS',
                    'qty' => 7,
                    'harga' => 1500,
                    'diskon1_persen' => 0,
                    'diskon2_persen' => 0,
                    'diskon3_persen' => 0,
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 3. Verify warehouse stock is untouched (remains 40, NOT 33!)
        $this->barang->refresh();
        $this->assertEquals(40.00, (float)$this->barang->stok);

        // 4. Verify canvas session detail has updated qty_terjual to 7
        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $detail = CanvasSessionDetail::where('canvas_session_id', $session->id)->first();
        $this->assertEquals(7.00, (float)$detail->qty_terjual);
        $this->assertEquals(3.00, (float)$detail->selisih); // qty_ambil(10) - qty_terjual(7) - qty_kembali(0) = 3
    }

    /**
     * Test closing a canvas session replenishes warehouse stock with returned items.
     */
    public function test_closing_canvas_replenishes_warehouse_stock(): void
    {
        // 1. First, load 10 units onto canvas
        $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        $detail = CanvasSessionDetail::where('canvas_session_id', $session->id)->first();

        // 2. Mock 7 units sold
        $detail->qty_terjual = 7.00;
        $detail->save();

        // 3. Unload/return 3 units to the warehouse
        $response = $this->actingAs($this->admin)->put(route('canvas.update', $session->id), [
            'keterangan' => 'Unloaded in the evening',
            'details' => [
                [
                    'id' => $detail->id,
                    'qty_kembali' => 3
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 4. Verify warehouse stock is replenished by 3 units (from 40 to 43)
        $this->barang->refresh();
        $this->assertEquals(43.00, (float)$this->barang->stok);

        // 5. Verify session status is completed and selisih is 0
        $session->refresh();
        $detail->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertEquals(3.00, (float)$detail->qty_kembali);
        $this->assertEquals(0.00, (float)$detail->selisih); // 10 - 7 - 3 = 0

        // Stock mutation log should exist
        $this->assertDatabaseHas('stok_mutasi', [
            'kode_barang' => $this->barang->kode_barang,
            'jenis_transaksi' => 'Canvas Kembali',
            'qty_masuk' => 3.00
        ]);
    }

    /**
     * Test deleting a draft canvas session reverts all taken stock.
     */
    public function test_deleting_draft_canvas_reverts_taken_stock(): void
    {
        // 1. Load 10 units onto canvas (stock decreases to 40)
        $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        // 2. Delete the session
        $response = $this->actingAs($this->admin)->delete(route('canvas.destroy', $session->id));
        $response->assertRedirect();

        // 3. Verify stock returned to 50
        $this->barang->refresh();
        $this->assertEquals(50.00, (float)$this->barang->stok);

        // 4. Verify session is deleted
        $this->assertDatabaseMissing('canvas_sessions', ['id' => $session->id]);

        // Stock mutation log should exist for reversion
        $this->assertDatabaseHas('stok_mutasi', [
            'kode_barang' => $this->barang->kode_barang,
            'jenis_transaksi' => 'Batal Canvas Ambil',
            'qty_masuk' => 10.00
        ]);
    }

    /**
     * Test admin can edit canvas session details while it is pending approval.
     */
    public function test_admin_can_edit_pending_canvas_session(): void
    {
        // 1. Create a pending canvas session
        $session = CanvasSession::create([
            'no_canvas' => 'KVS-20260630-0001',
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'status' => 'pending',
            'keterangan' => 'KVS pending'
        ]);

        $detail = CanvasSessionDetail::create([
            'canvas_session_id' => $session->id,
            'kode_barang' => $this->barang->kode_barang,
            'satuan_id' => $this->satuan->id,
            'qty_ambil' => 10,
            'diskon_persen' => 0,
            'qty_terjual' => 0,
            'qty_kembali' => 0,
            'selisih' => 10
        ]);

        // 2. Admin gets the edit page - should succeed (status 200)
        $responseGet = $this->actingAs($this->admin)->get(route('canvas.edit', $session->id));
        $responseGet->assertStatus(200);

        // 3. Admin updates the qty_ambil and diskon_persen
        $responsePut = $this->actingAs($this->admin)->put(route('canvas.update', $session->id), [
            'keterangan' => 'Updated keterangan pending',
            'details' => [
                [
                    'id' => $detail->id,
                    'qty_ambil' => 15,
                    'diskon_persen' => 5
                ]
            ]
        ]);

        $responsePut->assertSessionHasNoErrors();
        $responsePut->assertRedirect(route('canvas.show', $session->id));

        // 4. Verify updates in the database
        $session->refresh();
        $detail->refresh();
        $this->assertEquals('pending', $session->status); // status should remain pending
        $this->assertEquals('Updated keterangan pending', $session->keterangan);
        $this->assertEquals(15.0, (float)$detail->qty_ambil);
        $this->assertEquals(5.0, (float)$detail->diskon_persen);
        $this->assertEquals(15.0, (float)$detail->selisih);

        // 5. Verify no stock mutation was logged yet
        $this->assertDatabaseMissing('stok_mutasi', [
            'kode_barang' => $this->barang->kode_barang
        ]);
    }

    /**
     * Test canvas salesman search only shows loaded items and correct remaining canvas stock.
     */
    public function test_canvas_salesman_search_only_shows_loaded_items_with_canvas_stock(): void
    {
        // 1. Create a second product
        $anotherBarang = Barang::create([
            'kode_barang' => 'BRG-ANR',
            'nama_barang' => 'Another Goods',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 100.00,
            'status' => 1
        ]);

        $anotherSatuan = BarangSatuan::create([
            'kode_barang' => $anotherBarang->kode_barang,
            'satuan' => 'PCS',
            'isi' => 1,
            'harga_pokok' => 1000,
            'harga_jual' => 1500
        ]);

        // 2. Load only the first product ($this->barang) into canvas session
        $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        // 3. Search as canvas salesman
        $response = $this->actingAs($this->salesman)->getJson(route('barang.search', ['q' => '']));
        $data = $response->json();

        // The canvas salesman should only see the loaded product ($this->barang), not the other product ($anotherBarang)
        $this->assertCount(1, $data);
        $this->assertEquals($this->barang->kode_barang, $data[0]['kode_barang']);
        $this->assertEquals(10.0, (float)$data[0]['stok']);
    }

    /**
     * Test mobile order validates and enforces canvas stock levels.
     */
    public function test_mobile_order_enforces_canvas_stock_limits(): void
    {
        // 1. Load 10 units onto canvas
        $this->actingAs($this->admin)->post(route('canvas.store'), [
            'kode_sales' => $this->salesman->nik,
            'tanggal' => date('Y-m-d'),
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'qty_ambil' => 10
                ]
            ]
        ]);

        $session = CanvasSession::where('kode_sales', $this->salesman->nik)->first();
        $this->actingAs($this->admin)->post(route('canvas.approve', $session->id));

        // 2. Setup check-in for the customer
        \App\Models\PenjualanCheckin::create([
            'kode_sales' => $this->salesman->nik,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'checkin' => now(),
            'lat' => '0',
            'lng' => '0'
        ]);

        // 3. Attempt to store a mobile order for 15 units (exceeding canvas stock of 10)
        $response = $this->actingAs($this->salesman)->post(route('mobile.order.store'), [
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'jenis_transaksi' => 'Tunai',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'satuan' => 'PCS',
                    'qty' => 15,
                    'harga' => 1500
                ]
            ]
        ]);

        // Should fail and redirect back with error message about insufficient canvas stock
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Stok DPB untuk barang', session('error'));

        // 4. Store a mobile order for 6 units (valid, within 10)
        $response2 = $this->actingAs($this->salesman)->post(route('mobile.order.store'), [
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'jenis_transaksi' => 'Tunai',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'satuan' => 'PCS',
                    'qty' => 6,
                    'harga' => 1500
                ]
            ]
        ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertRedirect(route('mobile.kunjungan.index'));
    }

    /**
     * Test canvas sales customer auto-approval and visibility restriction.
     */
    public function test_canvas_sales_customer_auto_approval_and_filtering(): void
    {
        // Setup regions so firstOrCreate works
        \App\Models\Wilayah::firstOrCreate(
            ['kode_wilayah' => 93],
            ['nama_wilayah' => 'Canvas Area']
        );
        \App\Models\SubWilayah::firstOrCreate(
            ['kode_wilayah' => 93],
            ['nama_wilayah' => 'Canvas Sub Area']
        );

        // 1. A canvas sales registers a customer: must be auto-approved and assigned to them
        $response = $this->actingAs($this->salesman)->post(route('mobile.pelanggan.store'), [
            'nama_pelanggan' => 'Toko Baru Canvas',
            'alamat_pelanggan' => 'Jl. Baru No. 1',
            'no_hp_pelanggan' => '08123456789',
            'kode_wilayah' => 93,
            'sub_wilayah' => 93,
            'metode_bayar' => 'Cash',
        ]);

        $response->assertRedirect(route('mobile.kunjungan.index'));
        $response->assertSessionHas('success', 'Pelanggan baru berhasil didaftarkan dan siap dikunjungi.');

        $newCustomer = Pelanggan::where('nama_pelanggan', 'Toko Baru Canvas')->first();
        $this->assertNotNull($newCustomer);
        $this->assertEquals(1, $newCustomer->approve);
        $this->assertEquals($this->salesman->nik, $newCustomer->kode_sales);

        // 2. A regular/different canvas sales (e.g. Sales Canva B) should not see this customer in search
        $canvasSalesmanB = User::create([
            'name' => 'Sales Kanvas 2',
            'email' => 'sales2@test.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS-002',
            'role' => 'sales',
            'status' => '1',
            'is_kanvas' => true
        ]);

        // Search as Sales Canvas B
        $responseSearchB = $this->actingAs($canvasSalesmanB)->getJson(route('pelanggan.search', ['q' => 'Toko Baru Canvas']));
        $responseSearchB->assertOk();
        $resultsB = $responseSearchB->json();
        $this->assertCount(0, $resultsB); // Canvas B cannot see Canvas A's customer

        // Search as Sales Canvas A (the owner)
        $responseSearchA = $this->actingAs($this->salesman)->getJson(route('pelanggan.search', ['q' => 'Toko Baru Canvas']));
        $responseSearchA->assertOk();
        $resultsA = $responseSearchA->json();
        $this->assertCount(1, $resultsA);
        $this->assertEquals($newCustomer->kode_pelanggan, $resultsA[0]['id']);

        // 3. Admin panel sales transaction validation:
        // Choosing Canvas B salesman with Canvas A's customer should fail validation
        $responseAdminStoreFail = $this->actingAs($this->admin)->post(route('penjualan.store'), [
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $newCustomer->kode_pelanggan,
            'kode_sales' => $canvasSalesmanB->nik, // Canvas B
            'jenis_transaksi' => 'Tunai',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'satuan' => 'PCS',
                    'qty' => 1,
                    'harga' => 1500
                ]
            ]
        ]);
        $responseAdminStoreFail->assertRedirect();
        $responseAdminStoreFail->assertSessionHas('error');
        $this->assertStringContainsString('bukan merupakan pelanggan milik sales canvas', session('error'));

        // Choosing Canvas A salesman with Canvas A's customer should succeed
        $responseAdminStoreSuccess = $this->actingAs($this->admin)->post(route('penjualan.store'), [
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $newCustomer->kode_pelanggan,
            'kode_sales' => $this->salesman->nik, // Canvas A
            'jenis_transaksi' => 'Tunai',
            'items' => [
                [
                    'kode_barang' => $this->barang->kode_barang,
                    'satuan_id' => $this->satuan->id,
                    'satuan' => 'PCS',
                    'qty' => 1,
                    'harga' => 1500
                ]
            ]
        ]);
        $responseAdminStoreSuccess->assertSessionHasNoErrors();
    }
}
