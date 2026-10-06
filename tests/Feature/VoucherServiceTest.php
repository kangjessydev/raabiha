<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VoucherService $voucherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voucherService = app(VoucherService::class);
    }

    public function test_validate_voucher_rejects_inactive_or_expired(): void
    {
        $inactiveVoucher = Voucher::create([
            'code' => 'INACTIVE',
            'name' => 'Inactive Voucher',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => false,
        ]);

        $res1 = $this->voucherService->validateVoucher($inactiveVoucher, 100000);
        $this->assertFalse($res1['valid']);

        $expiredVoucher = Voucher::create([
            'code' => 'EXPIRED',
            'name' => 'Expired Voucher',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $res2 = $this->voucherService->validateVoucher($expiredVoucher, 100000);
        $this->assertFalse($res2['valid']);
    }

    public function test_validate_voucher_enforces_channel_restrictions(): void
    {
        $posOnly = Voucher::create([
            'code' => 'POSONLY',
            'name' => 'Kasir Only',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'usable_channel' => 'pos_only',
            'is_active' => true,
        ]);

        $res = $this->voucherService->validateVoucher($posOnly, 100000, 1, channel: 'ecommerce');
        $this->assertFalse($res['valid']);
        $this->assertStringContainsString('kasir/POS', $res['message']);

        $resValid = $this->voucherService->validateVoucher($posOnly, 100000, 1, channel: 'pos');
        $this->assertTrue($resValid['valid']);
    }

    public function test_calculate_discount_respects_max_discount_cap(): void
    {
        $percentVoucher = Voucher::create([
            'code' => 'CAP50',
            'name' => 'Diskon 50% Max 20rb',
            'discount_type' => 'percent',
            'discount_amount' => 50,
            'max_discount' => 20000,
            'is_active' => true,
        ]);

        // 50% dari 100.000 = 50.000, tetapi di-cap ke 20.000
        $discount = $this->voucherService->calculateDiscount($percentVoucher, 100000);
        $this->assertEquals(20000, $discount);
    }

    public function test_verify_and_calculate_stacks_product_and_shipping_vouchers(): void
    {
        $prodVoucher = Voucher::create([
            'code' => 'PROD10',
            'name' => 'Diskon Produk',
            'discount_type' => 'fixed',
            'discount_amount' => 15000,
            'is_active' => true,
            'is_shipping_voucher' => false,
            'is_stackable' => true,
        ]);

        $shipVoucher = Voucher::create([
            'code' => 'ONGKIR10',
            'name' => 'Gratis Ongkir',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => true,
            'is_shipping_voucher' => true,
            'is_stackable' => true,
        ]);

        $result = $this->voucherService->verifyAndCalculate(
            [
                ['code' => 'PROD10'],
                ['code' => 'ONGKIR10'],
            ],
            subtotal: 100000,
            shippingCost: 20000,
            itemCount: 2
        );

        $this->assertEquals(15000, $result['product_discount']);
        $this->assertEquals(10000, $result['shipping_discount']);
        $this->assertEquals(25000, $result['total_discount']);
        $this->assertCount(2, $result['verified_vouchers']);
    }
}
