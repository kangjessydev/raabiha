<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PosSession;
use App\Models\Order;
use Livewire\Livewire;
use App\Livewire\PosManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        \App\Models\Product::create([
            'name' => 'Sample Product',
            'slug' => 'sample-product',
            'price' => 50000,
            'stock' => 100,
            'is_active' => true,
        ]);
        $this->artisan('db:seed', ['--class' => 'PosDummyDataSeeder']);
    }

    public function test_pos_history_filters_work_dynamically()
    {
        $kasir = User::where('email', 'kasir2@raabiha.com')->first();
        $this->actingAs($kasir);

        $test = Livewire::test(PosManager::class);

        // 1. Initial filter is 'shift' -> should see 8 orders in active session
        $test->assertSet('historyDateFilter', 'shift')
             ->assertViewHas('sessionOrders', function ($orders) {
                 return count($orders) === 8;
             });

        // 2. Change date filter to 'yesterday' -> should see yesterday's orders
        $test->set('historyDateFilter', 'yesterday')
             ->assertViewHas('sessionOrders', function ($orders) {
                 return count($orders) > 0 && count($orders) < 10;
             });

        // 3. Change date filter to 'all' -> should see 10 orders
        $test->set('historyDateFilter', 'all')
             ->assertViewHas('sessionOrders', function ($orders) {
                 return count($orders) === 10;
             });

        // 4. Filter by payment 'cash' -> should filter cash orders only
        $test->set('historyPaymentFilter', 'cash')
             ->assertViewHas('sessionOrders', function ($orders) {
                 return count($orders) > 0 && $orders->every(fn($o) => in_array($o->payment_method, ['cash', 'tunai']));
             });

        // 5. Search for specific order number '0005'
        $test->set('historySearch', '0005')
             ->assertViewHas('sessionOrders', function ($orders) {
                 return count($orders) === 1 && str_contains($orders->first()->order_number, '0005');
             });
    }
}
