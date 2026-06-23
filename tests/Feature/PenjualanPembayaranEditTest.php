<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\ActivityLog;
use App\Models\PenjualanPembayaran;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PenjualanPembayaranEditTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $salesman;
    private $pelanggan;
    private $penjualan;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Create Admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'nik' => 'ADM001',
            'status' => '1',
        ]);
        $this->admin->assignRole($adminRole);

        // Create Salesman
        $this->salesman = User::factory()->create([
            'role' => 'sales',
            'nik' => 'SLS001',
            'status' => '1',
        ]);

        // Create Pelanggan
        $this->pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'Pelanggan Test',
            'status' => '1',
        ]);

        // Create Penjualan (INV-001)
        $this->penjualan = Penjualan::create([
            'no_faktur' => 'INV-001',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'jenis_transaksi' => 'Kredit',
            'total' => 1000000,
            'diskon' => 0,
            'grand_total' => 1000000,
            'id_user' => $this->admin->id,
            'kode_sales' => $this->salesman->nik,
            'batal' => 0,
        ]);
    }

    public function test_unauthorized_user_cannot_edit_payment(): void
    {
        // 1. Guest user
        $response = $this->post(route('pembayaran.update-payment', ['id' => 1, 'source' => 'cash']), [
            'tanggal' => date('Y-m-d'),
            'no_bukti' => 'BKK-001',
            'jumlah' => 100000,
            'kode_sales' => $this->salesman->nik,
        ]);
        $response->assertRedirect('/login');

        // 2. Salesman user (non-admin role)
        $response = $this->actingAs($this->salesman)->post(route('pembayaran.update-payment', ['id' => 1, 'source' => 'cash']), [
            'tanggal' => date('Y-m-d'),
            'no_bukti' => 'BKK-001',
            'jumlah' => 100000,
            'kode_sales' => $this->salesman->nik,
        ]);
        $response->assertRedirect(route('mobile.dashboard'));
    }

    public function test_admin_can_edit_cash_payment(): void
    {
        $payment = PenjualanPembayaran::create([
            'no_bukti' => 'BKK-001',
            'tanggal' => date('Y-m-d'),
            'no_faktur' => $this->penjualan->no_faktur,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'jenis_bayar' => 'Tunai',
            'jumlah' => 500000,
            'keterangan' => 'Initial payment',
            'id_user' => $this->admin->id,
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($this->admin)->post(route('pembayaran.update-payment', ['id' => $payment->id, 'source' => 'cash']), [
            'tanggal' => '2026-06-25',
            'no_bukti' => 'BKK-001-EDITED',
            'jumlah' => 600000,
            'kode_sales' => $this->salesman->nik,
            'keterangan' => 'Updated payment',
        ]);

        $response->assertRedirect(route('penjualan.show', $this->penjualan->no_faktur));
        $response->assertSessionHas('success', 'Pembayaran berhasil diperbarui.');

        $payment->refresh();
        $this->assertEquals('BKK-001-EDITED', $payment->no_bukti);
        $this->assertEquals('2026-06-25', $payment->tanggal->format('Y-m-d'));
        $this->assertEquals(600000, (float)$payment->jumlah);
        $this->assertEquals('Updated payment', $payment->keterangan);

        // Assert Activity Log is created
        $this->assertTrue(ActivityLog::where('action', 'Edit Pembayaran')
            ->where('no_faktur', $this->penjualan->no_faktur)
            ->exists());
    }

    public function test_admin_can_edit_transfer_payment(): void
    {
        DB::table('penjualan_pembayaran_transfer')->insert([
            'kode_transfer' => 'TRF-001',
            'no_faktur' => $this->penjualan->no_faktur,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'jenis_bayar' => 'Transfer',
            'jumlah' => 300000,
            'tanggal' => date('Y-m-d'),
            'status' => 'pending',
            'keterangan' => 'TRF original',
            'id_user' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('pembayaran.update-payment', ['id' => 'TRF-001', 'source' => 'transfer']), [
            'tanggal' => '2026-06-26',
            'no_bukti' => 'TRF-001-NEW',
            'jumlah' => 400000,
            'kode_sales' => $this->salesman->nik,
            'keterangan' => 'TRF edited',
        ]);

        $response->assertRedirect(route('penjualan.show', $this->penjualan->no_faktur));

        $updated = DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', 'TRF-001-NEW')->first();
        $this->assertNotNull($updated);
        $this->assertEquals('2026-06-26', $updated->tanggal);
        $this->assertEquals(400000, (float)$updated->jumlah);
        $this->assertEquals('TRF edited', $updated->keterangan);

        // Ensure old key is gone
        $this->assertFalse(DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', 'TRF-001')->exists());
    }

    public function test_admin_cannot_exceed_remaining_piutang(): void
    {
        // Invoice grand total is 1,000,000.
        // Let's create an approved payment of 800,000.
        $payment = PenjualanPembayaran::create([
            'no_bukti' => 'BKK-001',
            'tanggal' => date('Y-m-d'),
            'no_faktur' => $this->penjualan->no_faktur,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'jenis_bayar' => 'Tunai',
            'jumlah' => 800000,
            'id_user' => $this->admin->id,
            'status' => 'disetujui',
        ]);

        // Attempting to edit this payment to 1,100,000 (which exceeds the 1,000,000 grand total)
        $response = $this->actingAs($this->admin)->post(route('pembayaran.update-payment', ['id' => $payment->id, 'source' => 'cash']), [
            'tanggal' => date('Y-m-d'),
            'no_bukti' => 'BKK-001-EDITED',
            'jumlah' => 1100000,
            'kode_sales' => $this->salesman->nik,
            'keterangan' => 'Too much',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Assert the database payment was NOT updated
        $payment->refresh();
        $this->assertEquals(800000, (float)$payment->jumlah);
    }

    public function test_duplicate_transfer_key_validation(): void
    {
        DB::table('penjualan_pembayaran_transfer')->insert([
            'kode_transfer' => 'TRF-EXISTING',
            'no_faktur' => $this->penjualan->no_faktur,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'jumlah' => 100000,
            'tanggal' => date('Y-m-d'),
            'status' => 'pending',
            'id_user' => $this->admin->id,
        ]);

        DB::table('penjualan_pembayaran_transfer')->insert([
            'kode_transfer' => 'TRF-TARGET',
            'no_faktur' => $this->penjualan->no_faktur,
            'kode_pelanggan' => $this->pelanggan->kode_pelanggan,
            'kode_sales' => $this->salesman->nik,
            'jumlah' => 200000,
            'tanggal' => date('Y-m-d'),
            'status' => 'pending',
            'id_user' => $this->admin->id,
        ]);

        // Try to rename TRF-TARGET to TRF-EXISTING
        $response = $this->actingAs($this->admin)->post(route('pembayaran.update-payment', ['id' => 'TRF-TARGET', 'source' => 'transfer']), [
            'tanggal' => date('Y-m-d'),
            'no_bukti' => 'TRF-EXISTING',
            'jumlah' => 200000,
            'kode_sales' => $this->salesman->nik,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Kode Transfer / No Bukti sudah digunakan.');

        // Verify database is untouched
        $this->assertTrue(DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', 'TRF-TARGET')->exists());
    }
}
