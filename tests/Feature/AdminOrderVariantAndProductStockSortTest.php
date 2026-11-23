<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminOrderVariantAndProductStockSortTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'email' => 'admin-p2@raabiha.test',
        ]);
        $this->admin->assignRole('super_admin');
    }

    public function test_create_order_requires_variant_for_variant_product(): void
    {
        $product = Product::create([
            'name' => 'Gamis Varian Wajib',
            'slug' => 'gamis-varian-wajib',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hitam - M',
            'stock' => 10,
            'selling_price' => 210000,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateOrder::class)
            ->fillForm([
                'source' => 'offline',
                'status' => 'completed',
                'payment_status' => 'paid',
                'shipping_address' => ['is_pickup' => true],
                'subtotal' => 200000,
                'shipping_cost' => 0,
                'discount_total' => 0,
                'grand_total' => 200000,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'name' => $product->name,
                        'price' => 200000,
                        'quantity' => 1,
                        'total' => 200000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.product_variant_id' => 'required']);
    }

    public function test_create_order_rejects_variant_from_different_product_and_inactive_variant(): void
    {
        $productA = Product::create([
            'name' => 'Gamis Produk A',
            'slug' => 'gamis-produk-a',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $inactiveVariantA = ProductVariant::create([
            'product_id' => $productA->id,
            'name' => 'A - Non Aktif',
            'stock' => 5,
            'selling_price' => 200000,
            'is_active' => false,
        ]);

        $productB = Product::create([
            'name' => 'Gamis Produk B',
            'slug' => 'gamis-produk-b',
            'price' => 220000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $variantB = ProductVariant::create([
            'product_id' => $productB->id,
            'name' => 'B - Aktif',
            'stock' => 8,
            'selling_price' => 220000,
            'is_active' => true,
        ]);

        // Cross-product variant ID must be rejected
        Livewire::actingAs($this->admin)
            ->test(CreateOrder::class)
            ->fillForm([
                'source' => 'offline',
                'status' => 'completed',
                'payment_status' => 'paid',
                'shipping_address' => ['is_pickup' => true],
                'subtotal' => 200000,
                'shipping_cost' => 0,
                'discount_total' => 0,
                'grand_total' => 200000,
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'product_variant_id' => $variantB->id,
                        'name' => $productA->name,
                        'price' => 200000,
                        'quantity' => 1,
                        'total' => 200000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.product_variant_id']);

        // Inactive variant on new order must be rejected
        Livewire::actingAs($this->admin)
            ->test(CreateOrder::class)
            ->fillForm([
                'source' => 'offline',
                'status' => 'completed',
                'payment_status' => 'paid',
                'shipping_address' => ['is_pickup' => true],
                'subtotal' => 200000,
                'shipping_cost' => 0,
                'discount_total' => 0,
                'grand_total' => 200000,
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'product_variant_id' => $inactiveVariantA->id,
                        'name' => $productA->name,
                        'price' => 200000,
                        'quantity' => 1,
                        'total' => 200000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.product_variant_id']);
    }

    public function test_create_order_accepts_valid_active_variant_and_standalone_product(): void
    {
        $variantProduct = Product::create([
            'name' => 'Gamis Valid Varian',
            'slug' => 'gamis-valid-varian',
            'price' => 250000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $activeVariant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'name' => 'Sage - L',
            'stock' => 12,
            'selling_price' => 260000,
            'is_active' => true,
        ]);

        $standaloneProduct = Product::create([
            'name' => 'Bros Mutiara',
            'slug' => 'bros-mutiara',
            'price' => 50000,
            'stock' => 20,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateOrder::class)
            ->fillForm([
                'source' => 'offline',
                'status' => 'completed',
                'payment_status' => 'paid',
                'shipping_address' => ['is_pickup' => true],
                'subtotal' => 310000,
                'shipping_cost' => 0,
                'discount_total' => 0,
                'grand_total' => 310000,
                'items' => [
                    [
                        'product_id' => $variantProduct->id,
                        'product_variant_id' => $activeVariant->id,
                        'name' => 'Gamis Valid Varian - Sage - L',
                        'price' => 260000,
                        'quantity' => 1,
                        'total' => 260000,
                    ],
                    [
                        'product_id' => $standaloneProduct->id,
                        'product_variant_id' => null,
                        'name' => 'Bros Mutiara',
                        'price' => 50000,
                        'quantity' => 1,
                        'total' => 50000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'product_id' => $variantProduct->id,
            'product_variant_id' => $activeVariant->id,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $standaloneProduct->id,
            'product_variant_id' => null,
        ]);
    }

    public function test_edit_order_preserves_historical_inactive_variant(): void
    {
        $product = Product::create([
            'name' => 'Gamis Historis',
            'slug' => 'gamis-historis',
            'price' => 180000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $inactiveVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Navy - S',
            'stock' => 0,
            'selling_price' => 180000,
            'is_active' => false,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-HIST-001',
            'source' => 'offline',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 180000,
            'shipping_cost' => 0,
            'discount_total' => 0,
            'grand_total' => 180000,
            'shipping_address' => ['is_pickup' => true],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $inactiveVariant->id,
            'name' => 'Gamis Historis - Navy - S',
            'price' => 180000,
            'quantity' => 1,
            'total' => 180000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $inactiveVariant->id,
        ]);
    }

    public function test_changing_product_in_order_form_resets_selected_variant(): void
    {
        $productA = Product::create([
            'name' => 'Gamis Reset A',
            'slug' => 'gamis-reset-a',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $variantA = ProductVariant::create([
            'product_id' => $productA->id,
            'name' => 'Varian A',
            'stock' => 10,
            'selling_price' => 205000,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'name' => 'Gamis Reset B',
            'slug' => 'gamis-reset-b',
            'price' => 150000,
            'stock' => 15,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(CreateOrder::class);

        $items = $component->get('data.items');
        $firstItemKey = array_key_first($items);

        $component
            ->set("data.items.{$firstItemKey}.product_id", $productA->id)
            ->set("data.items.{$firstItemKey}.product_variant_id", $variantA->id)
            ->assertSet("data.items.{$firstItemKey}.product_variant_id", $variantA->id)
            ->set("data.items.{$firstItemKey}.product_id", $productB->id)
            ->assertSet("data.items.{$firstItemKey}.product_variant_id", null);
    }

    public function test_products_table_sorts_stock_by_active_variant_sum_and_formats_active_only(): void
    {
        // Standalone product with stock = 10
        $standaloneTen = Product::create([
            'name' => 'Produk Biasa 10',
            'slug' => 'produk-biasa-10',
            'price' => 100000,
            'stock' => 10,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        // Variant product with raw products.stock = 0, active variants = 25 (15 + 10), inactive = 100
        $variantHigh = Product::create([
            'name' => 'Produk Varian 25',
            'slug' => 'produk-varian-25',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);
        ProductVariant::create([
            'product_id' => $variantHigh->id,
            'name' => 'Aktif 1',
            'stock' => 15,
            'selling_price' => 200000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $variantHigh->id,
            'name' => 'Aktif 2',
            'stock' => 10,
            'selling_price' => 200000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $variantHigh->id,
            'name' => 'Non-Aktif Besar',
            'stock' => 100,
            'selling_price' => 200000,
            'is_active' => false,
        ]);

        // Variant product with raw products.stock = 50 (stale), active variants = 3, inactive = 99
        $variantLow = Product::create([
            'name' => 'Produk Varian 3',
            'slug' => 'produk-varian-3',
            'price' => 150000,
            'stock' => 50,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);
        ProductVariant::create([
            'product_id' => $variantLow->id,
            'name' => 'Aktif Kecil',
            'stock' => 3,
            'selling_price' => 150000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $variantLow->id,
            'name' => 'Non-Aktif 99',
            'stock' => 99,
            'selling_price' => 150000,
            'is_active' => false,
        ]);

        // Sort DESC: 25 -> 10 -> 3
        Livewire::actingAs($this->admin)
            ->test(ListProducts::class)
            ->sortTable('stock', 'desc')
            ->assertCanSeeTableRecords([$variantHigh, $standaloneTen, $variantLow], inOrder: true)
            ->assertSee('25 pcs (varian)')
            ->assertSee('10 pcs')
            ->assertSee('3 pcs (varian)')
            ->assertDontSee('125 pcs (varian)')
            ->assertDontSee('102 pcs (varian)');

        // Sort ASC: 3 -> 10 -> 25
        Livewire::actingAs($this->admin)
            ->test(ListProducts::class)
            ->sortTable('stock', 'asc')
            ->assertCanSeeTableRecords([$variantLow, $standaloneTen, $variantHigh], inOrder: true);
    }
}
