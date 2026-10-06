<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\StockLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'owner', 'finance'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_tripay_webhook_rejects_invalid_signature()
    {
        SiteSetting::create([
            'key' => 'tripay_private_key',
            'value' => 'secret-tripay-key',
        ]);

        $response = $this->withHeaders([
            'X-Callback-Signature' => 'invalid-signature',
            'X-Callback-Event' => 'payment_status',
        ])->postJson('/webhook/tripay', [
            'merchant_ref' => 'ORD-TEST-001',
            'status' => 'PAID',
        ]);

        $response->assertStatus(403);
    }

    public function test_tripay_webhook_marks_order_paid_and_is_idempotent()
    {
        $privateKey = 'secret-tripay-key';
        SiteSetting::create([
            'key' => 'tripay_private_key',
            'value' => $privateKey,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TRIPAY-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'shipping_cost' => 0,
            'shipping_address' => [],
        ]);

        $payload = json_encode([
            'merchant_ref' => $order->order_number,
            'reference' => 'TP-REF-123',
            'status' => 'PAID',
        ]);
        $signature = hash_hmac('sha256', $payload, $privateKey);

        $response = $this->call(
            'POST',
            '/webhook/tripay',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('packed', $order->status);

        // Second call with same payload should succeed idempotently
        $secondResponse = $this->call(
            'POST',
            '/webhook/tripay',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );
        $secondResponse->assertStatus(200);
    }

    public function test_tripay_webhook_restores_stock_on_expired_without_duplication()
    {
        $privateKey = 'secret-tripay-key';
        SiteSetting::create([
            'key' => 'tripay_private_key',
            'value' => $privateKey,
        ]);

        $product = Product::create([
            'name' => 'Baju Koko Modern',
            'slug' => 'baju-koko-modern',
            'stock' => 5,
            'price' => 100000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TRIPAY-EXPIRED',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'shipping_cost' => 0,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 100000,
            'quantity' => 3,
            'total' => 300000,
        ]);

        $payload = json_encode([
            'merchant_ref' => $order->order_number,
            'reference' => 'TP-REF-EXP',
            'status' => 'EXPIRED',
        ]);
        $signature = hash_hmac('sha256', $payload, $privateKey);

        $response = $this->call(
            'POST',
            '/webhook/tripay',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);
        $order->refresh();
        $product->refresh();

        $this->assertEquals('failed', $order->payment_status);
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals(8, $product->stock); // 5 + 3 restored

        // Panggilan kedua tidak boleh menambah stok lagi
        $secondResponse = $this->call(
            'POST',
            '/webhook/tripay',
            [],
            [],
            [],
            [
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );
        $secondResponse->assertStatus(200);
        $product->refresh();
        $this->assertEquals(8, $product->stock); // Tetap 8, tidak jadi 11
    }

    public function test_xendit_webhook_rejects_invalid_token()
    {
        SiteSetting::create([
            'key' => 'xendit_webhook_token',
            'value' => 'valid-xendit-token',
        ]);

        $response = $this->withHeaders([
            'x-callback-token' => 'wrong-token',
        ])->postJson('/webhook/xendit', [
            'external_id' => 'ORD-XENDIT-001',
            'status' => 'PAID',
        ]);

        $response->assertStatus(403);
    }

    public function test_xendit_webhook_marks_order_paid_and_restores_stock_on_failed()
    {
        $token = 'valid-xendit-token';
        SiteSetting::create([
            'key' => 'xendit_webhook_token',
            'value' => $token,
        ]);

        $product = Product::create([
            'name' => 'Khimar Muslimah',
            'slug' => 'khimar-muslimah',
            'stock' => 10,
            'price' => 75000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-XENDIT-002',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 75000,
            'grand_total' => 75000,
            'shipping_cost' => 0,
            'shipping_address' => [],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 75000,
            'quantity' => 2,
            'total' => 150000,
        ]);

        // Kirim webhook FAILED
        $response = $this->withHeaders([
            'x-callback-token' => $token,
        ])->postJson('/webhook/xendit', [
            'external_id' => $order->order_number,
            'status' => 'FAILED',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $product->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('failed', $order->payment_status);
        $this->assertEquals(12, $product->stock); // 10 + 2 restored

        // Panggilan kedua tidak boleh dobel restore
        $response2 = $this->withHeaders([
            'x-callback-token' => $token,
        ])->postJson('/webhook/xendit', [
            'external_id' => $order->order_number,
            'status' => 'FAILED',
        ]);
        $response2->assertStatus(200);
        $product->refresh();
        $this->assertEquals(12, $product->stock);
    }
}
