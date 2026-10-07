<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Voucher;
use App\Services\OrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderStockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'owner', 'finance', 'logistics', 'cashier', 'admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_order_cancellation_restores_stock_and_creates_stock_log(): void
    {
        $product = Product::create([
            'name' => 'Gamis Silk Luxury',
            'slug' => 'gamis-silk-luxury',
            'stock' => 10,
            'price' => 150000,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'stock' => 5,
            'selling_price' => 150000,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STOCK-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'shipping_cost' => 10000,
            'grand_total' => 110000,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'name' => 'Variant Item',
            'price' => 50000,
            'quantity' => 2,
            'total' => 100000,
        ]);

        $this->assertFalse((bool) $order->is_stock_restored);
        $this->assertEquals(5, $variant->fresh()->stock);

        // Ubah status ke cancelled (simulasi pembatalan via observer / admin)
        $order->update(['status' => 'cancelled']);

        $order->refresh();
        $this->assertTrue((bool) $order->is_stock_restored);
        $this->assertNotNull($order->stock_restored_at);
        $this->assertEquals(7, $variant->fresh()->stock); // 5 + 2

        $stockLog = StockLog::where('product_variant_id', $variant->id)->first();
        $this->assertNotNull($stockLog);
        $this->assertEquals('in', $stockLog->type);
        $this->assertEquals(2, $stockLog->quantity_change);
        $this->assertEquals(7, $stockLog->quantity_after);
    }

    public function test_stock_restoration_is_idempotent_and_prevents_double_restoration(): void
    {
        $product = Product::create([
            'name' => 'Khimar Premium',
            'slug' => 'khimar-premium',
            'stock' => 20,
            'price' => 50000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STOCK-002',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'grand_total' => 60000,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Simple Product',
            'price' => 50000,
            'quantity' => 3,
            'total' => 50000,
        ]);

        $service = app(OrderStockService::class);

        // Pemanggilan pertama
        $firstCall = $service->restoreStock($order);
        $this->assertTrue($firstCall);
        $this->assertEquals(23, $product->fresh()->stock); // 20 + 3

        // Pemanggilan kedua (misal webhook tiba setelah admin cancel)
        $secondCall = $service->restoreStock($order);
        $this->assertFalse($secondCall); // Ditolak oleh Idempotency Guard

        // Stok TIDAK bertambah lagi
        $this->assertEquals(23, $product->fresh()->stock);
        $this->assertEquals(1, StockLog::where('product_id', $product->id)->count());
    }

    public function test_voucher_used_count_is_restored_upon_cancellation(): void
    {
        $voucher = Voucher::create([
            'code' => 'DISC50',
            'name' => 'Diskon 50K',
            'discount_type' => 'fixed',
            'discount_amount' => 50000,
            'is_active' => true,
            'used_count' => 1,
            'max_uses' => 10,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STOCK-003',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 150000,
            'shipping_cost' => 10000,
            'voucher_id' => $voucher->id,
            'applied_voucher_ids' => [$voucher->id],
            'discount_total' => 50000,
            'grand_total' => 110000,
            'shipping_address' => [],
        ]);

        $this->assertEquals(1, $voucher->fresh()->used_count);

        $order->update(['status' => 'cancelled']);

        $this->assertEquals(0, $voucher->fresh()->used_count);
    }

    public function test_customer_cancelling_order_in_account_restores_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Abaya Elegan',
            'slug' => 'abaya-elegan',
            'stock' => 8,
            'price' => 80000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-STOCK-004',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 80000,
            'shipping_cost' => 10000,
            'grand_total' => 90000,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Customer Item',
            'price' => 80000,
            'quantity' => 2,
            'total' => 80000,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Account::class)
            ->set('cancelOrderId', $order->id)
            ->call('cancelOrder');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertTrue((bool) $order->is_stock_restored);
        $this->assertEquals(10, $product->fresh()->stock); // 8 + 2
    }

    public function test_deleting_order_restores_stock_if_not_already_restored(): void
    {
        $product = Product::create([
            'name' => 'Tunik Harian',
            'slug' => 'tunik-harian',
            'stock' => 15,
            'price' => 75000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STOCK-DEL-01',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 75000,
            'shipping_cost' => 10000,
            'grand_total' => 85000,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => 'Tunik Item',
            'price' => 75000,
            'quantity' => 3,
            'total' => 75000,
        ]);

        $this->assertEquals(15, $product->fresh()->stock);

        // Hapus pesanan langsung (simulasi DeleteAction di Filament)
        $order->delete();

        // Stok produk harus kembali bertambah (15 + 3 = 18)
        $this->assertEquals(18, $product->fresh()->stock);

        $log = StockLog::where('product_id', $product->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('in', $log->type);
        $this->assertEquals(3, $log->quantity_change);
        $this->assertEquals('order_deleted', $log->reason);
    }
}
