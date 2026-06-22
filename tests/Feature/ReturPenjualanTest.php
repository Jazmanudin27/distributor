<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ReturPenjualan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_retur_penjualan_auto_generated_number_format(): void
    {
        // Create user
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);

        // Get the /retur-penjualan/create page
        $response = $this->actingAs($user)->get(route('retur-penjualan.create'));

        $response->assertStatus(200);

        // Assert the generated number follows the RP + YYMM + XXXX pattern
        $expectedPrefix = 'RP' . date('ym');
        $expectedNoReturFirst = $expectedPrefix . '0001';
        $response->assertSee($expectedNoReturFirst);

        // Let's create an existing ReturPenjualan record
        $pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'Test Pelanggan',
            'status' => '1',
        ]);

        ReturPenjualan::create([
            'no_retur' => $expectedNoReturFirst,
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'total' => 0,
            'user_id' => $user->id,
        ]);

        // Get the create page again
        $response = $this->actingAs($user)->get(route('retur-penjualan.create'));
        $response->assertStatus(200);

        // Assert it increments to 0002
        $expectedNoReturSecond = $expectedPrefix . '0002';
        $response->assertSee($expectedNoReturSecond);
    }

    public function test_retur_penjualan_invoices_by_customer_ajax(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);

        $pelanggan1 = Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'Pelanggan 1',
            'status' => '1',
        ]);

        $pelanggan2 = Pelanggan::create([
            'kode_pelanggan' => 'PLG002',
            'nama_pelanggan' => 'Pelanggan 2',
            'status' => '1',
        ]);

        // Invoices for pelanggan1
        \App\Models\Penjualan::create([
            'no_faktur' => 'INV001',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan1->kode_pelanggan,
            'jenis_transaksi' => 'T',
            'jenis_bayar' => 'Tunai',
            'total' => 100000,
            'diskon' => 0,
            'grand_total' => 100000,
            'id_user' => $user->id,
            'batal' => 0,
        ]);

        \App\Models\Penjualan::create([
            'no_faktur' => 'INV002',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan1->kode_pelanggan,
            'jenis_transaksi' => 'T',
            'jenis_bayar' => 'Tunai',
            'total' => 150000,
            'diskon' => 0,
            'grand_total' => 150000,
            'id_user' => $user->id,
            'batal' => 0,
        ]);

        // Canceled invoice for pelanggan1 (should not be returned)
        \App\Models\Penjualan::create([
            'no_faktur' => 'INV-CANCEL',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan1->kode_pelanggan,
            'jenis_transaksi' => 'T',
            'jenis_bayar' => 'Tunai',
            'total' => 200000,
            'diskon' => 0,
            'grand_total' => 200000,
            'id_user' => $user->id,
            'batal' => 1,
        ]);

        // Invoice for pelanggan2
        \App\Models\Penjualan::create([
            'no_faktur' => 'INV003',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan2->kode_pelanggan,
            'jenis_transaksi' => 'T',
            'jenis_bayar' => 'Tunai',
            'total' => 300000,
            'diskon' => 0,
            'grand_total' => 300000,
            'id_user' => $user->id,
            'batal' => 0,
        ]);

        // Query invoices for pelanggan1
        $response = $this->actingAs($user)->getJson(route('penjualan.by-pelanggan', [
            'kode_pelanggan' => $pelanggan1->kode_pelanggan
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(2); // INV001 and INV002
        $response->assertJsonFragment(['no_faktur' => 'INV001', 'grand_total' => '100000.00']);
        $response->assertJsonFragment(['no_faktur' => 'INV002', 'grand_total' => '150000.00']);
        $response->assertJsonMissing(['no_faktur' => 'INV-CANCEL']);
        $response->assertJsonMissing(['no_faktur' => 'INV003']);
    }
}
