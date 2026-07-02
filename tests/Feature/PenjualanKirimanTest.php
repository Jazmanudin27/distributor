<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class PenjualanKirimanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Add unique constraint on users(nik) to prevent SQLite foreign key mismatch
        Schema::table('users', function ($table) {
            $table->unique('nik');
        });
    }

    public function test_get_invoices_excludes_canvas_sales(): void
    {
        // 1. Setup role and permissions for Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $user = User::factory()->create([
            'role' => 'Super Admin',
            'status' => '1',
        ]);
        $user->assignRole($superAdminRole);

        // 2. Setup Wilayah
        $wilayah = Wilayah::create([
            'kode_wilayah' => 999,
            'nama_wilayah' => 'Wilayah Test',
        ]);

        // 3. Setup Pelanggan under this Wilayah
        $pelanggan = Pelanggan::create([
            'kode_pelanggan' => 'PLG-999',
            'nama_pelanggan' => 'Pelanggan Test',
            'kode_wilayah' => $wilayah->kode_wilayah,
            'status' => '1',
        ]);

        // 4. Setup two salesmen: one regular and one canvas
        $salesReguler = User::factory()->create([
            'role' => 'Salesman',
            'nik' => 'SLS-REG',
            'is_kanvas' => 0,
            'status' => '1',
        ]);

        $salesKanvas = User::factory()->create([
            'role' => 'Salesman',
            'nik' => 'SLS-KANV',
            'is_kanvas' => 1,
            'status' => '1',
        ]);

        // 5. Create two invoices (Penjualan)
        $invoiceReguler = Penjualan::create([
            'no_faktur' => 'INV-REG-1',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'jenis_transaksi' => 'Kredit',
            'total' => 100000,
            'diskon' => 0,
            'grand_total' => 100000,
            'id_user' => $user->id,
            'kode_sales' => $salesReguler->nik,
            'batal' => 0,
        ]);

        $invoiceKanvas = Penjualan::create([
            'no_faktur' => 'INV-KANV-2',
            'tanggal' => date('Y-m-d'),
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'jenis_transaksi' => 'Kredit',
            'total' => 200000,
            'diskon' => 0,
            'grand_total' => 200000,
            'id_user' => $user->id,
            'kode_sales' => $salesKanvas->nik,
            'batal' => 0,
        ]);

        // 6. Request the invoices list via AJAX (no date filters to avoid timezone discrepancies in test environment)
        $response = $this->actingAs($user)->getJson(route('penjualan-kiriman.get-invoices', [
            'kode_wilayah' => $wilayah->kode_wilayah,
        ]));

        // 7. Verify assertions
        $response->assertStatus(200);
        
        // Assert regular invoice is in the list
        $response->assertJsonFragment([
            'no_faktur' => 'INV-REG-1',
        ]);

        // Assert canvas invoice is NOT in the list
        $response->assertJsonMissing([
            'no_faktur' => 'INV-KANV-2',
        ]);
    }
}
