<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\PosSession;
use Livewire\Livewire;
use App\Livewire\PosManager;
use Spatie\Permission\Models\Role;

class PosTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected PosSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'kasir']);
        $this->cashier = User::factory()->create(['pos_pin' => bcrypt('123456')]);
        $this->cashier->assignRole($role);

        $this->session = PosSession::create([
            'cashier_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);
    }

    public function test_history_displays_orders_and_filters_by_search(): void
    {
        Order::create([
            'order_number' => 'POS-20260725-0001',
            'pos_session_id' => $this->session->id,
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Sudi Sudiarto',
            'customer_phone' => '081234567890',
            'payment_method' => 'cash',
            'subtotal' => 150000,
            'grand_total' => 150000,
            'status' => 'completed',
            'source' => 'pos',
        ]);

        Order::create([
            'order_number' => 'POS-20260725-0002',
            'pos_session_id' => $this->session->id,
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '089988776655',
            'payment_method' => 'qris',
            'subtotal' => 250000,
            'grand_total' => 250000,
            'status' => 'completed',
            'source' => 'pos',
        ]);

        Livewire::actingAs($this->cashier)
            ->test(PosManager::class)
            ->assertSee('POS-20260725-0001')
            ->assertSee('POS-20260725-0002')
            ->set('historySearch', 'Sudi')
            ->assertSee('POS-20260725-0001')
            ->assertDontSee('POS-20260725-0002');
    }

    public function test_history_filters_by_payment_method_and_status(): void
    {
        Order::create([
            'order_number' => 'POS-20260725-0003',
            'pos_session_id' => $this->session->id,
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'cash',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'status' => 'completed',
            'source' => 'pos',
        ]);

        Order::create([
            'order_number' => 'POS-20260725-0004',
            'pos_session_id' => $this->session->id,
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'qris',
            'subtotal' => 200000,
            'grand_total' => 200000,
            'status' => 'cancelled',
            'source' => 'pos',
        ]);

        Livewire::actingAs($this->cashier)
            ->test(PosManager::class)
            ->set('historyPaymentFilter', 'cash')
            ->assertSee('POS-20260725-0003')
            ->assertDontSee('POS-20260725-0004')
            ->set('historyPaymentFilter', 'non_cash')
            ->assertSee('POS-20260725-0004')
            ->assertDontSee('POS-20260725-0003')
            ->set('historyPaymentFilter', 'all')
            ->set('historyStatusFilter', 'cancelled')
            ->assertSee('POS-20260725-0004')
            ->assertDontSee('POS-20260725-0003');
    }
}
