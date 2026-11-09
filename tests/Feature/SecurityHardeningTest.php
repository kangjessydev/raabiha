<?php

namespace Tests\Feature;

use App\Livewire\Account;
use App\Livewire\Cart;
use App\Models\Cart as CartModel;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\Voucher;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_unauthorized_user_cannot_access_invoice()
    {
        $user = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ORD-20261108-TEST1',
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'grand_total' => 100000,
        ]);

        // Anonymous request without session should get 403
        $response = $this->get('/invoice/'.$order->order_number);
        $response->assertStatus(403);

        // Different logged in user should also get 403
        $otherUser = User::factory()->create();
        $response = $this->actingAs($otherUser)->get('/invoice/'.$order->order_number);
        $response->assertStatus(403);

        // Owner can access
        $response = $this->actingAs($user)->get('/invoice/'.$order->order_number);
        $response->assertStatus(200);
    }

    public function test_account_cannot_view_other_users_refund_status()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order2 = Order::create([
            'order_number' => 'ORD-20261108-TEST2',
            'user_id' => $user2->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 200000,
            'grand_total' => 200000,
        ]);

        $refund2 = RefundRequest::create([
            'user_id' => $user2->id,
            'order_id' => $order2->id,
            'reason' => 'Defect item',
            'description' => 'Jahitan rusak',
            'refund_amount' => 200000,
            'status' => 'pending',
            'bank_name' => 'BCA',
            'bank_account_name' => 'User Two',
            'bank_account_number' => '1234567890',
        ]);

        // User 1 tries to open User 2's refund status
        Livewire::actingAs($user1)
            ->test(Account::class)
            ->call('openRefundStatus', $refund2->id)
            ->assertSet('showRefundStatusModal', false)
            ->assertSet('refundStatusData', null);

        // User 2 opens their own refund status
        Livewire::actingAs($user2)
            ->test(Account::class)
            ->call('openRefundStatus', $refund2->id)
            ->assertSet('showRefundStatusModal', true)
            ->assertNotSet('refundStatusData', null);
    }

    public function test_cart_item_mutation_is_scoped_to_active_cart()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $product = Product::create([
            'name' => 'Gamis Silk',
            'slug' => 'gamis-silk',
            'price' => 150000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart1 = CartModel::create(['user_id' => $user1->id, 'is_buy_now' => false]);
        $cart2 = CartModel::create(['user_id' => $user2->id, 'is_buy_now' => false]);

        $item2 = CartItem::create([
            'cart_id' => $cart2->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // User 1 tries to increment/delete item belonging to Cart 2
        Livewire::actingAs($user1)
            ->test(Cart::class)
            ->call('incrementQuantity', $item2->id)
            ->call('removeItem', $item2->id);

        // Item 2 in Cart 2 should remain untouched
        $this->assertDatabaseHas('cart_items', [
            'id' => $item2->id,
            'quantity' => 2,
        ]);
    }

    public function test_pos_voucher_discount_is_calculated_server_side()
    {
        $product = Product::create([
            'name' => 'Baju Koko',
            'slug' => 'baju-koko',
            'price' => 100000,
            'pos_price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Create voucher: 10% discount (max Rp 15.000)
        $voucher = Voucher::create([
            'name' => 'Diskon 10 Persen',
            'code' => 'PROMO10',
            'discount_type' => 'percent',
            'discount_amount' => 10,
            'max_discount' => 15000,
            'min_purchase' => 50000,
            'is_active' => true,
            'usable_channel' => 'both',
        ]);

        $cashier = User::factory()->create();
        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $payload = [
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_name' => 'Budi',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 100000,
                ],
            ],
            'payment_method' => 'cash',
            'cash_paid' => 100000,
            'voucher_id' => $voucher->id,
            // Attacker tries to claim Rp 80.000 discount instead of legitimate Rp 10.000
            'voucher_discount' => 80000,
        ];

        $service = app(PosTransactionService::class);
        $order = $service->completePosTransaction($payload);

        // Server recalculates: 10% of 100.000 = 10.000
        $this->assertEquals(10000, $order->discount_total);
        $this->assertEquals(90000, $order->grand_total);
    }
}
