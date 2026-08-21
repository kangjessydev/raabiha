<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\User;
use App\Services\EscPosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosReprintReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_esc_pos_service_generates_reprint_watermark_when_is_reprint_is_true()
    {
        $cashier = User::factory()->create(['name' => 'Kasir Test']);
        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $order = Order::create([
            'order_number' => 'POS-20260723-0001',
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'source' => 'pos',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $escPosService = new EscPosService();
        $normalBase64 = $escPosService->generateReceipt($order, isReprint: false);
        $reprintBase64 = $escPosService->generateReceipt($order, isReprint: true);

        $normalText = base64_decode($normalBase64);
        $reprintText = base64_decode($reprintBase64);

        $this->assertStringNotContainsString('*** SALINAN / REPRINT ***', $normalText);
        $this->assertStringContainsString('*** SALINAN / REPRINT ***', $reprintText);
    }
}
