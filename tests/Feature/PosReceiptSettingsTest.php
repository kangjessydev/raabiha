<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosCustomer;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\EscPosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosReceiptSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);

        $this->cashier = User::factory()->create([
            'name' => 'Budi Kasir',
            'email' => 'budikasir@raabiha.test',
        ]);
        $this->cashier->assignRole('kasir');

        SiteSetting::updateOrCreate(['key' => 'pos_receipt_header'], ['value' => "BOUTIQUE RAABIHA\nJl. Merdeka No. 45 Bandung"]);
        SiteSetting::updateOrCreate(['key' => 'pos_receipt_footer'], ['value' => "Terima Kasih Atas Kunjungan Anda\nInstagram: @raabihasby"]);
        SiteSetting::updateOrCreate(['key' => 'pos_show_cashier_name'], ['value' => '1']);
        SiteSetting::updateOrCreate(['key' => 'pos_show_date'], ['value' => '1']);
        SiteSetting::updateOrCreate(['key' => 'pos_paper_size'], ['value' => '58']);
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_enabled'], ['value' => '1']);
    }

    public function test_receipt_text_reflects_custom_header_footer_and_settings()
    {
        $order = Order::create([
            'order_number' => 'POS-20260725-TEST1',
            'user_id' => null,
            'cashier_id' => $this->cashier->id,
            'source' => 'pos',
            'subtotal' => 150000,
            'grand_total' => 150000,
            'cash_paid' => 200000,
            'cash_change' => 50000,
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081234567890',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'name' => 'Gamis Silk Premium',
            'product_name' => 'Gamis Silk Premium',
            'variant_name' => 'Navy - L',
            'quantity' => 1,
            'price' => 150000,
            'total' => 150000,
        ]);

        PosCustomer::create([
            'name' => 'Siti Nurhaliza',
            'phone' => '081234567890',
            'stamp_count' => 3,
            'points' => 30,
        ]);

        $service = new EscPosService();
        $text = $service->generateReceiptText($order);

        $this->assertStringContainsString('BOUTIQUE RAABIHA', $text);
        $this->assertStringContainsString('Jl. Merdeka No. 45 Bandung', $text);
        $this->assertStringContainsString('Terima Kasih Atas Kunjungan Anda', $text);
        $this->assertStringContainsString('Kasir : Budi Kasir', $text);
        $this->assertStringContainsString('Plgn  : Siti Nurhaliza', $text);
        $this->assertStringContainsString('Gamis Silk Premium', $text);
        $this->assertStringContainsString('KARTU CAP DIGITAL RAABIHA', $text);
        $this->assertStringContainsString('Total Cap: 3 dari 9 Cap', $text);
    }

    public function test_receipt_text_hides_cashier_and_loyalty_when_disabled_in_settings()
    {
        SiteSetting::updateOrCreate(['key' => 'pos_show_cashier_name'], ['value' => '0']);
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_enabled'], ['value' => '0']);

        $order = Order::create([
            'order_number' => 'POS-20260725-TEST2',
            'user_id' => null,
            'cashier_id' => $this->cashier->id,
            'source' => 'pos',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'cash_paid' => 100000,
            'cash_change' => 0,
            'customer_name' => 'Ani Sulastri',
            'customer_phone' => '089876543210',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'name' => 'Hijab Instan',
            'product_name' => 'Hijab Instan',
            'quantity' => 1,
            'price' => 100000,
            'total' => 100000,
        ]);

        PosCustomer::create([
            'name' => 'Ani Sulastri',
            'phone' => '089876543210',
            'stamp_count' => 1,
            'points' => 10,
        ]);

        $service = new EscPosService();
        $text = $service->generateReceiptText($order);

        $this->assertStringNotContainsString('Kasir :', $text);
        $this->assertStringNotContainsString('KARTU CAP DIGITAL RAABIHA', $text);
    }

    public function test_receipt_text_adapts_to_80mm_paper_width()
    {
        SiteSetting::updateOrCreate(['key' => 'pos_paper_size'], ['value' => '80']);

        $order = Order::create([
            'order_number' => 'POS-20260725-TEST3',
            'source' => 'pos',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
        ]);

        $service = new EscPosService();
        $text = $service->generateReceiptText($order);

        // Lines for 80mm divider should use 48 dashes
        $this->assertStringContainsString(str_repeat('-', 48), $text);
    }
}
