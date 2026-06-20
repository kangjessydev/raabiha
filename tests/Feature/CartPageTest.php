<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_renders_successfully()
    {
        $response = $this->get('/cart');
        $response->assertStatus(200);
    }

    public function test_cart_livewire_component_can_render()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200)
            ->assertViewHas('subtotal')
            ->assertViewHas('grandTotal');
    }
}
