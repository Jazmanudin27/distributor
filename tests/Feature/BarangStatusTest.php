<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Barang;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BarangStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin role and assign to user
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permissionEdit = Permission::firstOrCreate(['name' => 'edit-barang']);
        $adminRole->givePermissionTo($permissionEdit);

        $this->user = User::factory()->create([
            'role' => 'admin',
            'status' => '1',
        ]);
        $this->user->assignRole($adminRole);
    }

    public function test_admin_can_toggle_barang_status(): void
    {
        $barang = Barang::create([
            'kode_barang' => 'BRG-001',
            'nama_barang' => 'Test Barang A',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 10,
            'status' => 1, // Active initially
        ]);

        $response = $this->actingAs($this->user)->post(route('barang.toggle-status', $barang->kode_barang));
        $response->assertRedirect();
        
        $barang->refresh();
        $this->assertEquals(0, $barang->status); // Now inactive

        // Toggle back to active
        $response = $this->actingAs($this->user)->post(route('barang.toggle-status', $barang->kode_barang));
        $response->assertRedirect();
        
        $barang->refresh();
        $this->assertEquals(1, $barang->status); // Active again
    }

    public function test_admin_can_bulk_deactivate_barang(): void
    {
        $barang1 = Barang::create([
            'kode_barang' => 'BRG-001',
            'nama_barang' => 'Test Barang A',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 10,
            'status' => 1,
        ]);

        $barang2 = Barang::create([
            'kode_barang' => 'BRG-002',
            'nama_barang' => 'Test Barang B',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 5,
            'status' => 1,
        ]);

        $barang3 = Barang::create([
            'kode_barang' => 'BRG-003',
            'nama_barang' => 'Test Barang C',
            'kategori' => 'Kategori A',
            'merk' => 'Merk X',
            'stok' => 12,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->user)->post(route('barang.bulk-deactivate', [
            'selected_ids' => ['BRG-001', 'BRG-003']
        ]));

        $response->assertRedirect();

        $barang1->refresh();
        $barang2->refresh();
        $barang3->refresh();

        $this->assertEquals(0, $barang1->status); // Deactivated
        $this->assertEquals(1, $barang2->status); // Remains active
        $this->assertEquals(0, $barang3->status); // Deactivated
    }
}
