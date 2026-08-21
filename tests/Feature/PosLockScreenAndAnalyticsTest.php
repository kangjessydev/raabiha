<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\User;
use App\Livewire\PosManager;
use App\Filament\Widgets\DashboardStatsOverview;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosLockScreenAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_cashier_must_setup_pin_and_can_lock_unlock_and_change_pin()
    {
        $cashier = User::factory()->create([
            'password' => bcrypt('secret123'),
            'pos_pin'  => null, // belum punya PIN
        ]);

        // 1. Initial PIN Setup Mandatory check
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->assertSet('hasPosPin', false)
            ->set('posPinInput', '123456')
            ->set('posPinConfirm', '123456')
            ->call('saveInitialPosPin')
            ->assertDispatched('pin-created')
            ->assertSet('hasPosPin', true);

        $cashier->refresh();
        $this->assertTrue(Hash::check('123456', $cashier->pos_pin));

        // 2. Lock screen unlock with wrong PIN 6-digit -> fails
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('unlockScreenWithPin', '999999')
            ->assertDispatched('screen-unlock-failed');

        // 3. Lock screen unlock with correct PIN 6-digit -> succeeds
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('unlockScreenWithPin', '123456')
            ->assertDispatched('screen-unlocked');

        // 4. Change PIN 6-digit
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->set('oldPosPin', '123456')
            ->set('newPosPin', '654321')
            ->set('newPosPinConfirm', '654321')
            ->call('changePosPin')
            ->assertDispatched('pin-changed');

        $cashier->refresh();
        $this->assertTrue(Hash::check('654321', $cashier->pos_pin));
    }

    public function test_dashboard_stats_widget_filters_by_sales_channel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Create Online Order
        Order::create([
            'order_number' => 'ORD-ONLINE-01',
            'source' => 'online',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'xendit',
            'created_at' => now(),
        ]);

        // Create POS Order
        Order::create([
            'order_number' => 'POS-20260723-0001',
            'source' => 'pos',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'created_at' => now(),
        ]);

        // Test Channel 'all': Total completed = 2, total amount = 150.000
        Livewire::test(DashboardStatsOverview::class)
            ->set('channel', 'all')
            ->assertSee('2 Pesanan');

        // Test Channel 'pos': Total completed = 1
        Livewire::test(DashboardStatsOverview::class)
            ->set('channel', 'pos')
            ->assertSee('1 Pesanan');

        // Test Channel 'online': Total completed = 1
        Livewire::test(DashboardStatsOverview::class)
            ->set('channel', 'online')
            ->assertSee('1 Pesanan');
    }
}
