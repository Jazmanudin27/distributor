<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Wilayah;
use App\Models\SubWilayah;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class RekapSisaPiutangTunaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekap_sisa_piutang_includes_unpaid_cash_sales(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permission = Permission::firstOrCreate(['name' => 'view-laporan_piutang']);
        $adminRole->givePermissionTo($permission);

        $user = User::factory()->create([
            'nik' => '999',
            'role' => 'admin',
            'status' => '1',
            'is_kanvas' => 0,
        ]);
        $user->assignRole($adminRole);

        $wilayah = Wilayah::create(['kode_wilayah' => 1, 'nama_wilayah' => 'Wilayah 1']);
        $subWilayah = SubWilayah::create(['kode_wilayah' => 1, 'nama_wilayah' => 'Sub 1']);

        $pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'CUST01',
            'nama_pelanggan' => 'Toko Cash Belum Lunas',
            'kode_wilayah' => 1,
            'sub_wilayah' => 1,
            'limit_pelanggan' => 10000000,
            'kode_sales' => $user->nik,
        ]);

        $today = date('Y-m-d');

        // Create a Cash sale ('T' or 'Tunai') with grand_total = 500000, zero payments (unpaid)
        $penjualanTunaiUnpaid = Penjualan::create([
            'no_faktur' => 'FK-TUNAI-01',
            'tanggal' => $today,
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'kode_sales' => $user->nik,
            'jenis_transaksi' => 'Tunai',
            'grand_total' => 500000,
            'subtotal' => 500000,
            'batal' => 0,
        ]);
        // Force string Y-m-d format in database
        DB::table('penjualan')->where('no_faktur', 'FK-TUNAI-01')->update(['tanggal' => $today]);

        // Create a Cash sale ('T' or 'Tunai') fully paid
        $penjualanTunaiPaid = Penjualan::create([
            'no_faktur' => 'FK-TUNAI-02',
            'tanggal' => $today,
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'kode_sales' => $user->nik,
            'jenis_transaksi' => 'T',
            'grand_total' => 300000,
            'subtotal' => 300000,
            'batal' => 0,
        ]);
        DB::table('penjualan')->where('no_faktur', 'FK-TUNAI-02')->update(['tanggal' => $today]);

        DB::table('penjualan_pembayaran')->insert([
            'no_faktur' => 'FK-TUNAI-02',
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'tanggal' => $today,
            'jumlah' => 300000,
            'jenis_bayar' => 'Cash',
            'status' => 'disetujui',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Query rekap sisa piutang cetak
        $response = $this->actingAs($user)->get(route('laporan.rekap-sisa-piutang.cetak', [
            'tanggal' => $today,
            'kategori_sales' => 'all',
        ]));

        $response->assertStatus(200);
        // The unpaid cash sale must appear in the report
        $response->assertSee('FK-TUNAI-01');
        $response->assertSee('Toko Cash Belum Lunas');

        // The fully paid cash sale must NOT appear
        $response->assertDontSee('FK-TUNAI-02');
    }
}
