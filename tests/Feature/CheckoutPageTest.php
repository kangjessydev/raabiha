<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_successfully()
    {
        $response = $this->get('/checkout');
        // If not logged in, checkout redirects to login or cart, let's see what happens
        $response->assertStatus(200);
    }
}
