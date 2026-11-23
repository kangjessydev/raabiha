<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Widgets\ProductStatsWidget;
use App\Livewire\PosManager;
use App\Models\Cashflow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class P3WidgetStockAndPosVoidNullSafeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'owner', 'kasir'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_product_stats_widget_counts_variant_product_as_out_of_stock_if_any_active_variant_is_zero(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // 1. Produk non-varian stok 0 -> HARUS DIHITUNG
        Product::create([
            'name' => 'Bros Habis',
            'slug' => 'bros-habis',
            'price' => 25000,
            'stock' => 0,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        // 2. Produk bervarian: 1 varian aktif stok 10 + 1 varian aktif stok 0 -> HARUS DIHITUNG (aturan user)
        $partialEmptyVariantProduct = Product::create([
            'name' => 'Gamis Sebagian Habis',
            'slug' => 'gamis-sebagian-habis',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);
        ProductVariant::create([
            'product_id' => $partialEmptyVariantProduct->id,
            'name' => 'Hitam - M',
            'stock' => 10,
            'selling_price' => 200000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $partialEmptyVariantProduct->id,
            'name' => 'Hitam - L',
            'stock' => 0,
            'selling_price' => 200000,
            'is_active' => true,
        ]);

        // 3. Produk bervarian: semua varian aktif berstok > 0, hanya varian NON-AKTIF yang 0 -> TIDAK DIHITUNG
        $healthyWithInactiveZero = Product::create([
            'name' => 'Gamis Sehat',
            'slug' => 'gamis-sehat',
            'price' => 210000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);
        ProductVariant::create([
            'product_id' => $healthyWithInactiveZero->id,
            'name' => 'Sage - M',
            'stock' => 5,
            'selling_price' => 210000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $healthyWithInactiveZero->id,
            'name' => 'Sage - Lama Non Aktif',
            'stock' => 0,
            'selling_price' => 210000,
            'is_active' => false,
        ]);

        // 4. Produk bervarian tanpa varian aktif sama sekali -> HARUS DIHITUNG
        $noActiveVariants = Product::create([
            'name' => 'Gamis Tanpa Varian Aktif',
            'slug' => 'gamis-tanpa-varian-aktif',
            'price' => 190000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);
        ProductVariant::create([
            'product_id' => $noActiveVariants->id,
            'name' => 'Non Aktif Saja',
            'stock' => 8,
            'selling_price' => 190000,
            'is_active' => false,
        ]);

        // Total yang harus terhitung = 3 (Bros Habis + Gamis Sebagian Habis + Gamis Tanpa Varian Aktif)
        Livewire::actingAs($admin)
            ->test(ProductStatsWidget::class)
            ->assertSeeInOrder(['Stok Habis / Menipis', '3', 'Produk/Varian yang perlu restock']);
    }

    public function test_pos_void_order_succeeds_without_crash_when_supervisor_pin_is_not_required(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'pos_require_pin_for_void'],
            ['value' => '0']
        );

        $cashier = User::factory()->create([
            'name' => 'Kasir Pagi',
            'pos_pin' => Hash::make('111111'),
        ]);
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Koko Kasir',
            'slug' => 'koko-kasir',
            'sku' => 'KOKO-01',
            'price' => 120000,
            'stock' => 4,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'pos_only',
        ]);

        $order = Order::create([
            'order_number' => 'POS-NOPIN-001',
            'source' => 'pos',
            'pos_session_id' => $session->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 240000,
            'grand_total' => 240000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 2,
            'price' => 120000,
            'total' => 240000,
        ]);

        Cashflow::create([
            'transaction_date' => now()->toDateString(),
            'type' => 'in',
            'category' => 'pos_sale',
            'amount' => 240000,
            'description' => 'Penjualan POS #POS-NOPIN-001',
            'order_id' => $order->id,
            'source' => 'pos',
            'is_reversed' => false,
        ]);

        // Void tanpa supervisorId & supervisorPin -> harus sukses tanpa crash null pointer
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $order->id, null, null, 'Batal oleh pelanggan')
            ->assertDispatched('order-voided');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals($cashier->id, $order->void_by_id);
        $this->assertEquals(6, $product->fresh()->stock); // 4 + 2

        $stockLog = StockLog::where('product_id', $product->id)->where('reason', 'pos_void')->first();
        $this->assertNotNull($stockLog);
        $this->assertStringContainsString('Oleh Kasir: Kasir Pagi', $stockLog->notes);

        $voidCashflow = Cashflow::where('order_id', $order->id)->where('category', 'pos_void')->first();
        $this->assertNotNull($voidCashflow);
        $this->assertStringContainsString('Oleh Kasir: Kasir Pagi', $voidCashflow->description);
    }
}
