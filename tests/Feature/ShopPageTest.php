<?php

namespace Tests\Feature;

use App\Livewire\Shop;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_renders_successfully()
    {
        $response = $this->get('/shop');
        $response->assertStatus(200);
    }

    public function test_shop_livewire_component_can_render()
    {
        Livewire::test(Shop::class)
            ->assertStatus(200)
            ->assertViewHas('products')
            ->assertViewHas('categories')
            ->assertViewHas('filterAttributes');
    }
}
