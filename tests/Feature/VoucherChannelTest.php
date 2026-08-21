<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use App\Livewire\Cart as CartComponent;
use App\Livewire\Checkout;
use App\Livewire\Account;
use App\Livewire\PosManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoucherChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_only_voucher_is_rejected_in_cart_and_checkout()
    {
        $voucherPos = Voucher::create([
            'name' => 'Diskon POS Sahaja',
            'code' => 'POSONLY50',
            'discount_type' => 'fixed',
            'discount_amount' => 50000,
            'is_active' => true,
            'usable_channel' => 'pos_only',
        ]);

        $user = User::factory()->create();

        // Test in Cart
        Livewire::actingAs($user)
            ->test(CartComponent::class)
            ->set('voucherCode', 'POSONLY50')
            ->call('applyVoucher')
            ->assertSee('Kode voucher ini hanya berlaku untuk transaksi di Kasir / POS.');

        // Test in Checkout
        Livewire::actingAs($user)
            ->test(Checkout::class)
            ->set('voucherCode', 'POSONLY50')
            ->call('applyVoucher')
            ->assertSee('Kode voucher ini hanya berlaku untuk transaksi di Kasir / POS.');
    }

    public function test_pos_only_voucher_is_filtered_out_from_online_lists()
    {
        $voucherPos = Voucher::create([
            'name' => 'Diskon POS Only',
            'code' => 'POSONLY',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => true,
            'usable_channel' => 'pos_only',
        ]);

        $voucherOnline = Voucher::create([
            'name' => 'Diskon Web Only',
            'code' => 'WEBONLY',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => true,
            'usable_channel' => 'online_only',
        ]);

        $voucherBoth = Voucher::create([
            'name' => 'Diskon Both',
            'code' => 'BOTHCHANNEL',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'is_active' => true,
            'usable_channel' => 'both',
        ]);

        $user = User::factory()->create();

        // Account page vouchers test
        $accountComponent = Livewire::actingAs($user)->test(Account::class);
        $accountVouchers = $accountComponent->get('vouchers');
        $this->assertTrue($accountVouchers->contains('id', $voucherOnline->id));
        $this->assertTrue($accountVouchers->contains('id', $voucherBoth->id));
        $this->assertFalse($accountVouchers->contains('id', $voucherPos->id));

        // POS Manager vouchers test
        $posComponent = Livewire::actingAs($user)->test(PosManager::class);
        $posVouchers = $posComponent->get('vouchers');
        $this->assertTrue($posVouchers->contains('id', $voucherPos->id));
        $this->assertTrue($posVouchers->contains('id', $voucherBoth->id));
        $this->assertFalse($posVouchers->contains('id', $voucherOnline->id));

        // Global promo labels test
        $promoLabels = Voucher::getGlobalPromoLabels();
        $labelTexts = collect($promoLabels)->pluck('text')->toArray();
        $this->assertContains('Voucher Rp10.000', $labelTexts);
    }
}
