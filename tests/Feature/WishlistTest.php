<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Livewire\WishlistToggle;
use App\Livewire\Account;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_wishlist_tab_redirects_to_login()
    {
        $response = $this->get('/account?activeTab=wishlist');
        $response->assertRedirect('/login');
    }

    public function test_user_can_access_wishlist_tab()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/account?activeTab=wishlist');
        $response->assertStatus(200);
    }

    public function test_can_toggle_wishlist_via_livewire()
    {
        $user = User::factory()->create();
        
        $category = Category::create([
            'name' => 'Dress',
            'slug' => 'dress',
        ]);
        
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Baju Raabiha',
            'slug' => 'baju-raabiha',
            'price' => 150000,
            'stock' => 10,
            'weight' => 200,
        ]);

        // Toggle wishlist (add)
        Livewire::actingAs($user)
            ->test(WishlistToggle::class, ['product_id' => $product->id])
            ->assertSet('isInWishlist', false)
            ->call('toggleWishlist')
            ->assertSet('isInWishlist', true)
            ->assertDispatched('wishlist-updated');

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Toggle wishlist (remove)
        Livewire::actingAs($user)
            ->test(WishlistToggle::class, ['product_id' => $product->id])
            ->assertSet('isInWishlist', true)
            ->call('toggleWishlist')
            ->assertSet('isInWishlist', false)
            ->assertDispatched('wishlist-updated');

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_update_profile()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        Livewire::actingAs($user)
            ->test(Account::class)
            ->set('name', 'New Name')
            ->set('email', 'new@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('oldpassword123'),
        ]);

        Livewire::actingAs($user)
            ->test(Account::class)
            ->set('current_password', 'oldpassword123')
            ->set('new_password', 'newpassword123')
            ->set('new_password_confirmation', 'newpassword123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $user->fresh()->password));
    }
}
