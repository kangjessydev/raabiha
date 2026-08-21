<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\User;
use App\Livewire\PosManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosVoidOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
    }

    public function test_cashier_can_void_pos_order_with_supervisor_pin_and_restock_product()
    {
        $owner = User::factory()->create(['pos_pin' => Hash::make('888888')]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create(['pos_pin' => Hash::make('111111')]);
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'T-Shirt Oversized Premium',
            'slug' => 't-shirt-oversized-premium',
            'sku' => 'TSH-OVR-01',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $order = Order::create([
            'order_number' => 'POS-20260723-9999',
            'source' => 'pos',
            'pos_session_id' => $session->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 2,
            'price' => 25000,
            'total' => 50000,
        ]);

        // Wrong supervisor PIN -> fails
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $order->id, $owner->id, '000000', 'Salah input barang')
            ->assertDispatched('notify');

        $order->refresh();
        $this->assertEquals('completed', $order->status);

        // Correct Supervisor PIN -> succeeds
        $test = Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $order->id, $owner->id, '888888', 'Pelanggan minta ganti barang');
        
        $test->assertDispatched('order-voided');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals($owner->id, $order->void_by_id);
        $this->assertEquals('Pelanggan minta ganti barang', $order->void_reason);

        // Stock restored: 10 + 2 = 12
        $product->refresh();
        $this->assertEquals(12, $product->stock);

        // Reversal Cashflow created
        $this->assertDatabaseHas('cashflows', [
            'order_id' => $order->id,
            'source' => 'pos',
            'category' => 'pos_void',
            'type' => 'out',
            'amount' => 50000,
            'is_reversed' => true,
        ]);
    }

    public function test_void_non_cash_order_records_cashflow_reversal()
    {
        $owner = User::factory()->create(['pos_pin' => Hash::make('888888')]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create(['pos_pin' => Hash::make('111111')]);
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $order = Order::create([
            'order_number' => 'POS-20260725-8888',
            'source' => 'pos',
            'pos_session_id' => $session->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 75000,
            'grand_total' => 75000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
        ]);

        // Void QRIS order
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $order->id, $owner->id, '888888', 'Void QRIS error')
            ->assertDispatched('order-voided');

        // Cashflow reversal should be created for non-cash void as well
        $this->assertDatabaseHas('cashflows', [
            'order_id' => $order->id,
            'source' => 'pos',
            'category' => 'pos_void',
            'type' => 'out',
            'amount' => 75000,
            'is_reversed' => true,
        ]);
    }
}
