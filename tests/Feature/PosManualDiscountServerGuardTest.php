<?php

namespace Tests\Feature;

use App\Livewire\PosManager;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\PosTransactionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosManualDiscountServerGuardTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $supervisor;

    protected PosSession $session;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'owner', 'kasir'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->supervisor = User::factory()->create([
            'name' => 'Owner POS',
            'pos_pin' => Hash::make('888888'),
        ]);
        $this->supervisor->assignRole('owner');

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Reguler',
            'pos_pin' => Hash::make('111111'),
        ]);
        $this->cashier->assignRole('kasir');

        $this->session = PosSession::create([
            'cashier_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $this->product = Product::create([
            'name' => 'Gamis POS 300K',
            'slug' => 'gamis-pos-300k',
            'sku' => 'GMS-POS-300',
            'price' => 300000,
            'stock' => 20,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'pos_only',
        ]);
    }

    protected function makePayload(float $manualDiscount, array $extra = []): array
    {
        return array_merge([
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'variant_id' => null,
                    'name' => $this->product->name,
                    'price' => 300000,
                    'quantity' => 1,
                ],
            ],
            'manual_discount' => $manualDiscount,
            'discount' => $manualDiscount,
            'voucher_discount' => 0,
            'payment_method' => 'cash',
            'cash_paid' => max(0, 300000 - $manualDiscount),
            'cash_change' => 0,
        ], $extra);
    }

    public function test_cashier_can_apply_manual_discount_within_threshold_without_supervisor_pin(): void
    {
        // Subtotal 300.000, diskon 30.000 (10% <= 20% dan <= Rp 50.000) -> sukses tanpa PIN
        Livewire::actingAs($this->cashier)
            ->test(PosManager::class)
            ->call('processCheckout', $this->makePayload(30000))
            ->assertDispatched('checkout-success');

        $this->assertEquals(1, Order::count());
        $this->assertEquals(270000, (float) Order::first()->grand_total);
    }

    public function test_cashier_cannot_bypass_manual_discount_threshold_even_with_forged_payload_field(): void
    {
        // Diskon 100.000 (> Rp 50.000 & > 20%) + mencoba memalsukan manual_discount_authorized_by dari klien
        Livewire::actingAs($this->cashier)
            ->test(PosManager::class)
            ->call('processCheckout', $this->makePayload(100000, [
                'manual_discount_authorized_by' => $this->supervisor->id,
            ]))
            ->assertNotDispatched('checkout-success')
            ->assertDispatched('notify');

        $this->assertEquals(0, Order::count());
    }

    public function test_cashier_with_verified_supervisor_pin_can_checkout_once_and_token_is_consumed(): void
    {
        $component = Livewire::actingAs($this->cashier)
            ->test(PosManager::class);

        // 1. Supervisor verifikasi PIN
        $component->call('verifySupervisorPin', $this->supervisor->id, '888888', 'general')
            ->assertDispatched('supervisor-authorized');

        // 2. Checkout diskon besar Rp 100.000 -> sukses & simpan jejak supervisor_id
        $component->call('processCheckout', $this->makePayload(100000))
            ->assertDispatched('checkout-success');

        $this->assertEquals(1, Order::count());
        $order = Order::first();
        $this->assertEquals(200000, (float) $order->grand_total);
        $details = is_array($order->payment_details) ? $order->payment_details : json_decode((string) $order->payment_details, true);
        $this->assertEquals($this->supervisor->id, $details['manual_discount_supervisor_id'] ?? null);

        // 3. Token sekali pakai sudah hangus: transaksi kedua dengan diskon besar tanpa verifikasi ulang harus ditolak
        $component->call('processCheckout', $this->makePayload(100000))
            ->assertNotDispatched('checkout-success');

        $this->assertEquals(1, Order::count());
    }

    public function test_direct_service_call_validates_supervisor_id_and_pin_for_large_manual_discount(): void
    {
        $service = new PosTransactionService();

        // Tanpa PIN -> gagal
        $failed = false;
        try {
            $service->completePosTransaction($this->makePayload(80000, [
                'cashier_id' => $this->cashier->id,
                'pos_session_id' => $this->session->id,
            ]));
        } catch (Exception $e) {
            $failed = true;
            $this->assertStringContainsString('Diskon manual melebihi batas', $e->getMessage());
        }
        $this->assertTrue($failed);

        // Dengan supervisor_id + supervisor_pin valid -> sukses
        $order = $service->completePosTransaction($this->makePayload(80000, [
            'cashier_id' => $this->cashier->id,
            'pos_session_id' => $this->session->id,
            'supervisor_id' => $this->supervisor->id,
            'supervisor_pin' => '888888',
        ]));

        $this->assertEquals(220000, (float) $order->grand_total);
        $details = is_array($order->payment_details) ? $order->payment_details : json_decode((string) $order->payment_details, true);
        $this->assertEquals($this->supervisor->id, $details['manual_discount_supervisor_id'] ?? null);
    }

    public function test_large_manual_discount_is_allowed_when_pin_setting_is_disabled(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'pos_require_pin_for_manual_discount'],
            ['value' => '0']
        );

        Livewire::actingAs($this->cashier)
            ->test(PosManager::class)
            ->call('processCheckout', $this->makePayload(100000))
            ->assertDispatched('checkout-success');

        $this->assertEquals(1, Order::count());
    }
}
