<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Barang;
use App\Models\StokMutasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class StokMutasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stok_mutasi_logging_updates_stock_and_creates_records(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);

        $barang = Barang::create([
            'kode_barang' => 'BRG0001',
            'nama_barang' => 'Barang Test',
            'stok' => 10,
            'status' => 1,
        ]);

        // 1. Log a purchase mutation
        DB::transaction(function() use ($barang, $user) {
            StokMutasi::log(
                $barang->kode_barang,
                date('Y-m-d'),
                'Pembelian',
                'TX-BELI-001',
                5.0, // masuk
                0.0, // keluar
                $user->id,
                'Test Pembelian'
            );
        });

        $barang->refresh();
        $this->assertEquals(15.0, (float)$barang->stok);

        $mutation = StokMutasi::where('no_referensi', 'TX-BELI-001')->first();
        $this->assertNotNull($mutation);
        $this->assertEquals(10.0, (float)$mutation->saldo_awal);
        $this->assertEquals(15.0, (float)$mutation->saldo_akhir);
        $this->assertEquals(5.0, (float)$mutation->qty_masuk);
        $this->assertEquals(0.0, (float)$mutation->qty_keluar);

        // 2. Log a sales mutation
        DB::transaction(function() use ($barang, $user) {
            StokMutasi::log(
                $barang->kode_barang,
                date('Y-m-d'),
                'Penjualan',
                'TX-JUAL-001',
                0.0, // masuk
                3.0, // keluar
                $user->id,
                'Test Penjualan'
            );
        });

        $barang->refresh();
        $this->assertEquals(12.0, (float)$barang->stok);

        $mutation2 = StokMutasi::where('no_referensi', 'TX-JUAL-001')->first();
        $this->assertNotNull($mutation2);
        $this->assertEquals(15.0, (float)$mutation2->saldo_awal);
        $this->assertEquals(12.0, (float)$mutation2->saldo_akhir);
        $this->assertEquals(0.0, (float)$mutation2->qty_masuk);
        $this->assertEquals(3.0, (float)$mutation2->qty_keluar);
    }

    public function test_laporan_stok_pages(): void
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view-laporan_stok']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);
        $user->assignRole($role);

        $barang = Barang::create([
            'kode_barang' => 'BRG0001',
            'nama_barang' => 'Barang Test',
            'stok' => 10,
            'status' => 1,
        ]);

        // Rekap report page
        $response = $this->actingAs($user)->get(route('laporan.stok', [
            'jenis_laporan' => 'rekap',
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_akhir' => date('Y-m-d'),
        ]));
        $response->assertStatus(200);

        // Rekap persediaan report page
        $response = $this->actingAs($user)->get(route('laporan.stok', [
            'jenis_laporan' => 'rekap_persediaan',
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_akhir' => date('Y-m-d'),
        ]));
        $response->assertStatus(200);

        // Detail report page (requires print/excel to trigger calculation)
        $response = $this->actingAs($user)->get(route('laporan.stok.cetak', [
            'jenis_laporan' => 'detail',
            'kode_barang' => $barang->kode_barang,
            'tanggal_mulai' => date('Y-m-d'),
            'tanggal_akhir' => date('Y-m-d'),
        ]));
        $response->assertStatus(200);
    }
}
