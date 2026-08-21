<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Filament\Pages\LaporanPenjualan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LaporanPenjualanFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_penjualan_channel_filters()
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // 1. Order E-Commerce Website (source = 'ecommerce')
        $orderEcommerce = Order::create([
            'order_number' => 'RBH-TEST-WEB',
            'source' => 'ecommerce',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 100000,
            'grand_total' => 100000,
        ]);

        // 2. Order POS Kasir (source = 'pos', pos_session_id = null)
        $orderPosNoSession = Order::create([
            'order_number' => 'POS-TEST-NOSESSION',
            'source' => 'pos',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 50000,
            'grand_total' => 50000,
        ]);

        // 3. Order Offline Admin (source = 'offline')
        $orderOffline = Order::create([
            'order_number' => 'RBH-TEST-OFFLINE',
            'source' => 'offline',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal' => 75000,
            'grand_total' => 75000,
        ]);

        // Test Filter Channel = 'pos'
        $posTest = Livewire::actingAs($admin)
            ->test(LaporanPenjualan::class)
            ->set('filters.channel', 'pos')
            ->call('applyFilters');

        $posTest->assertCanSeeTableRecords([$orderPosNoSession, $orderOffline]);
        $posTest->assertCanNotSeeTableRecords([$orderEcommerce]);

        // Test Filter Channel = 'online'
        $onlineTest = Livewire::actingAs($admin)
            ->test(LaporanPenjualan::class)
            ->set('filters.channel', 'online')
            ->call('applyFilters');

        $onlineTest->assertCanSeeTableRecords([$orderEcommerce]);
        $onlineTest->assertCanNotSeeTableRecords([$orderPosNoSession, $orderOffline]);

        // Test Filter Channel = 'all'
        $allTest = Livewire::actingAs($admin)
            ->test(LaporanPenjualan::class)
            ->set('filters.channel', 'all')
            ->call('applyFilters');

        $allTest->assertCanSeeTableRecords([$orderEcommerce, $orderPosNoSession, $orderOffline]);
    }
}
