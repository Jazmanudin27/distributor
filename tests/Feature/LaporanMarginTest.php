<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Barang;
use App\Models\BarangSatuan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LaporanMarginTest extends TestCase
{
    use RefreshDatabase;

    public function test_margin_report_displays_per_unit_data(): void
    {
        // 1. Setup roles and permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permission = Permission::firstOrCreate(['name' => 'view-laporan_stok']);
        $adminRole->givePermissionTo($permission);

        $user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);
        $user->assignRole($adminRole);

        // 2. Create a Barang with 2 units: DUS (isi 10) and PCS (isi 1)
        $barang = Barang::create([
            'kode_barang' => 'BRG-001',
            'nama_barang' => 'Barang Super',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 25.0, // 25 PCS = 2 DUS + 5 PCS
            'status' => 1,
        ]);

        $satuanDus = BarangSatuan::create([
            'kode_barang' => $barang->kode_barang,
            'satuan' => 'DUS',
            'isi' => 10,
            'harga_pokok' => 10000,
            'harga_jual' => 12000,
        ]);

        $satuanPcs = BarangSatuan::create([
            'kode_barang' => $barang->kode_barang,
            'satuan' => 'PCS',
            'isi' => 1,
            'harga_pokok' => 1100,
            'harga_jual' => 1300,
        ]);

        // 3. Request Laporan Margin
        $response = $this->actingAs($user)->get(route('laporan.stok.cetak', [
            'jenis_laporan' => 'margin',
        ]));

        $response->assertStatus(200);

        // 4. Assert unit split details in the output/view
        $response->assertSee('BRG-001');
        $response->assertSee('Barang Super');
        
        // Assert DUS line
        $response->assertSee('2');
        $response->assertSee('DUS');
        
        // Assert PCS line
        $response->assertSee('5');
        $response->assertSee('PCS');
    }
}
