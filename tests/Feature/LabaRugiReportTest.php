<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LabaRugiReportTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test Laba Rugi report page can be rendered for authorized users.
     */
    public function test_laba_rugi_report_page_and_variants(): void
    {
        // Find or create Owner role
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        
        // Ensure view-laporan_laba_rugi permission exists
        $permission = Permission::firstOrCreate(['name' => 'view-laporan_laba_rugi']);
        $ownerRole->givePermissionTo($permission);

        // Find or create owner user
        $user = User::firstOrCreate(
            ['email' => 'owner@distributor.com'],
            [
                'name' => 'Owner User',
                'password' => bcrypt('password'),
                'nik' => '005',
                'status' => '1',
                'role' => 'owner'
            ]
        );
        $user->assignRole($ownerRole);

        // Access the reports filter page
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi'));
        $response->assertStatus(200);

        // Access rekap print format
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi.cetak', [
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d'),
            'jenis_laporan' => 'rekap'
        ]));
        $response->assertStatus(200);

        // Access per_supplier print format
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi.cetak', [
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d'),
            'jenis_laporan' => 'per_supplier'
        ]));
        $response->assertStatus(200);

        // Access per_tanggal_supplier print format
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi.cetak', [
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d'),
            'jenis_laporan' => 'per_tanggal_supplier'
        ]));
        $response->assertStatus(200);

        // Access detail print format
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi.cetak', [
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d'),
            'jenis_laporan' => 'detail'
        ]));
        $response->assertStatus(200);

        // Access excel export formats
        $response = $this->actingAs($user)->get(route('laporan.laba-rugi.excel', [
            'tanggal_mulai' => date('Y-m-01'),
            'tanggal_akhir' => date('Y-m-d'),
            'jenis_laporan' => 'rekap'
        ]));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd-ms-excel');
    }
}
