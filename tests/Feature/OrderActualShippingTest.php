<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Cashflow;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderActualShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'owner', 'finance', 'logistics', 'cashier', 'admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_actual_shipping_cost_creates_cashflow_expense_on_order_create(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-SHIP-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_cost' => 15000,
            'actual_shipping_cost' => 18000,
            'courier' => 'jne',
            'grand_total' => 115000,
            'shipping_address' => [],
        ]);

        $cashflow = Cashflow::where('order_id', $order->id)
            ->where('payment_channel', 'shipping')
            ->first();

        $this->assertNotNull($cashflow);
        $this->assertEquals('out', $cashflow->type);
        $this->assertEquals('Shipping', $cashflow->category);
        $this->assertEquals(18000, (float) $cashflow->amount);
        $this->assertFalse($cashflow->is_reversed);
        $this->assertStringContainsString('JNE', $cashflow->description);
    }

    public function test_actual_shipping_cost_creates_or_updates_cashflow_expense_on_order_update(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-SHIP-002',
            'status' => 'pending',
            'payment_status' => 'paid',
            'subtotal' => 150000,
            'shipping_cost' => 20000,
            'grand_total' => 170000,
            'shipping_address' => [],
        ]);

        // Belum ada cashflow shipping
        $this->assertDatabaseMissing('cashflows', [
            'order_id' => $order->id,
            'payment_channel' => 'shipping',
        ]);

        // Admin memasukkan ongkir riil ekspedisi
        $order->update([
            'actual_shipping_cost' => 22000,
            'courier' => 'jnt',
            'awb_number' => 'JNT99887766',
        ]);

        $cashflow = Cashflow::where('order_id', $order->id)
            ->where('payment_channel', 'shipping')
            ->first();

        $this->assertNotNull($cashflow);
        $this->assertEquals(22000, (float) $cashflow->amount);
        $this->assertStringContainsString('JNT99887766', $cashflow->description);

        // Update nominal lagi (misal revisi berat di drop point)
        $order->update([
            'actual_shipping_cost' => 24000,
        ]);

        // Tetap hanya ada 1 record cashflow untuk shipping (idempotent / no duplicate)
        $this->assertEquals(1, Cashflow::where('order_id', $order->id)->where('payment_channel', 'shipping')->count());
        $this->assertEquals(24000, (float) $cashflow->fresh()->amount);
    }

    public function test_cashflow_shipping_expense_is_reversed_when_order_is_cancelled(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-SHIP-003',
            'status' => 'sent',
            'payment_status' => 'paid',
            'subtotal' => 200000,
            'shipping_cost' => 25000,
            'actual_shipping_cost' => 25000,
            'courier' => 'sicepat',
            'awb_number' => '001234567890',
            'grand_total' => 225000,
            'shipping_address' => [],
        ]);

        $cashflow = Cashflow::where('order_id', $order->id)
            ->where('payment_channel', 'shipping')
            ->first();

        $this->assertNotNull($cashflow);
        $this->assertFalse($cashflow->is_reversed);

        // Batalkan order
        $order->update(['status' => 'cancelled']);

        $this->assertTrue($cashflow->fresh()->is_reversed);
        $this->assertStringContainsString('dibatalkan', $cashflow->fresh()->reversal_note);
    }

    public function test_cashflow_shipping_expense_is_removed_when_actual_shipping_cost_cleared(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-SHIP-004',
            'status' => 'pending',
            'payment_status' => 'paid',
            'subtotal' => 100000,
            'shipping_cost' => 10000,
            'actual_shipping_cost' => 10000,
            'grand_total' => 110000,
            'shipping_address' => [],
        ]);

        $this->assertDatabaseHas('cashflows', [
            'order_id' => $order->id,
            'payment_channel' => 'shipping',
        ]);

        // Dikosongkan
        $order->update(['actual_shipping_cost' => null]);

        $this->assertDatabaseMissing('cashflows', [
            'order_id' => $order->id,
            'payment_channel' => 'shipping',
        ]);
    }

    public function test_logistics_and_finance_can_edit_actual_shipping_cost(): void
    {
        $logisticsUser = User::factory()->create();
        $logisticsUser->assignRole('logistics');

        $this->actingAs($logisticsUser);
        $method = new \ReflectionMethod(OrderForm::class, 'isFieldDisabled');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(null, 'actual_shipping_cost', 'edit'));
        $this->assertFalse($method->invoke(null, 'awb_number', 'edit'));
        $this->assertTrue($method->invoke(null, 'items', 'edit'));

        $financeUser = User::factory()->create();
        $financeUser->assignRole('finance');
        $this->actingAs($financeUser);

        $this->assertFalse($method->invoke(null, 'actual_shipping_cost', 'edit'));
        $this->assertFalse($method->invoke(null, 'payment_status', 'edit'));
        $this->assertTrue($method->invoke(null, 'items', 'edit'));
    }
}

