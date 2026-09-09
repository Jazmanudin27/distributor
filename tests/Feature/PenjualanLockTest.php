<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Http\Middleware\CheckPenjualanLock;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenjualanLockTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_is_time_in_window_logic()
    {
        // 19:00 to 06:00 (Overnight)
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('19:00', '19:00', '06:00'));
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('21:30', '19:00', '06:00'));
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('23:59', '19:00', '06:00'));
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('00:00', '19:00', '06:00'));
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('03:15', '19:00', '06:00'));
        $this->assertTrue(CheckPenjualanLock::isTimeInWindow('05:59', '19:00', '06:00'));

        // Outside window (open hours: 06:00 to 18:59)
        $this->assertFalse(CheckPenjualanLock::isTimeInWindow('06:00', '19:00', '06:00'));
        $this->assertFalse(CheckPenjualanLock::isTimeInWindow('08:00', '19:00', '06:00'));
        $this->assertFalse(CheckPenjualanLock::isTimeInWindow('12:00', '19:00', '06:00'));
        $this->assertFalse(CheckPenjualanLock::isTimeInWindow('18:59', '19:00', '06:00'));
    }

    public function test_sales_locked_at_1930_by_default_schedule()
    {
        Setting::setVal('auto_lock_penjualan_sales', '1');
        Setting::setVal('lock_sales_start', '19:00');
        Setting::setVal('lock_sales_end', '06:00');
        Setting::setVal('lock_penjualan_sales', '0');

        Carbon::setTestNow(Carbon::parse('2026-09-09 19:30:00'));

        $status = CheckPenjualanLock::isSalesLocked();
        $this->assertTrue($status['locked']);
        $this->assertEquals('schedule', $status['reason']);
        $this->assertStringContainsString('19:00', $status['message']);
        $this->assertStringContainsString('06:00', $status['message']);
    }

    public function test_sales_open_at_1000()
    {
        Setting::setVal('auto_lock_penjualan_sales', '1');
        Setting::setVal('lock_sales_start', '19:00');
        Setting::setVal('lock_sales_end', '06:00');
        Setting::setVal('lock_penjualan_sales', '0');

        Carbon::setTestNow(Carbon::parse('2026-09-09 10:00:00'));

        $status = CheckPenjualanLock::isSalesLocked();
        $this->assertFalse($status['locked']);
    }

    public function test_sales_open_if_auto_lock_disabled()
    {
        Setting::setVal('auto_lock_penjualan_sales', '0');
        Setting::setVal('lock_penjualan_sales', '0');

        Carbon::setTestNow(Carbon::parse('2026-09-09 21:00:00'));

        $status = CheckPenjualanLock::isSalesLocked();
        $this->assertFalse($status['locked']);
    }

    public function test_manual_lock_takes_precedence_during_daytime()
    {
        Setting::setVal('auto_lock_penjualan_sales', '1');
        Setting::setVal('lock_penjualan_sales', '1'); // manually locked

        Carbon::setTestNow(Carbon::parse('2026-09-09 11:00:00'));

        $status = CheckPenjualanLock::isSalesLocked();
        $this->assertTrue($status['locked']);
        $this->assertEquals('manual', $status['reason']);
    }

    public function test_mobile_order_route_blocked_during_night_hours()
    {
        Setting::setVal('auto_lock_penjualan_sales', '1');
        Setting::setVal('lock_sales_start', '19:00');
        Setting::setVal('lock_sales_end', '06:00');
        Setting::setVal('lock_penjualan_sales', '0');

        Carbon::setTestNow(Carbon::parse('2026-09-09 20:00:00'));

        $salesRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Sales']);
        $sales = User::create([
            'name' => 'Sales One',
            'email' => 'sales1@test.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS01',
            'status' => '1',
            'role' => 'sales',
        ]);
        $sales->assignRole($salesRole);

        // Web Request redirected to mobile dashboard
        $response = $this->actingAs($sales)->get(route('mobile.order.create'));
        $response->assertRedirect(route('mobile.dashboard'));
        $response->assertSessionHas('error');

        // AJAX / JSON request gets 403
        $jsonResponse = $this->actingAs($sales)->getJson(route('mobile.order.create'));
        $jsonResponse->assertStatus(403);
        $jsonResponse->assertJsonStructure(['error']);
    }

    public function test_dashboard_controller_set_lock_penjualan_persists_settings()
    {
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_lock@test.com',
            'password' => bcrypt('password'),
            'nik' => 'ADM099',
            'status' => '1',
            'role' => 'admin',
        ]);
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)->post(route('dashboard.set-lock-penjualan'), [
            'lock_penjualan_admin' => '1',
            'lock_penjualan_sales' => '1',
            'auto_lock_penjualan_sales' => '1',
            'lock_sales_start' => '19:00',
            'lock_sales_end' => '06:00',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1', Setting::getVal('lock_penjualan_admin'));
        $this->assertEquals('1', Setting::getVal('lock_penjualan_sales'));
        $this->assertEquals('1', Setting::getVal('auto_lock_penjualan_sales'));
        $this->assertEquals('19:00', Setting::getVal('lock_sales_start'));
        $this->assertEquals('06:00', Setting::getVal('lock_sales_end'));
    }

    public function test_sales_with_semua_merk_restriction_allowed()
    {
        $salesRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Sales']);
        $sales = User::create([
            'name' => 'Sales Merk',
            'email' => 'sales_merk@test.com',
            'password' => bcrypt('password'),
            'nik' => 'SLS02',
            'status' => '1',
            'role' => 'sales',
            'jenis_sales' => 'merk',
            'jenis_barang' => 'semua',
        ]);
        $sales->assignRole($salesRole);

        $allowedItems = array_map('trim', explode(',', $sales->jenis_barang ?? ''));
        $this->assertTrue(in_array('semua', $allowedItems));
    }
}

