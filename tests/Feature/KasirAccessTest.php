<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KasirAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup roles
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_kasir_user_is_redirected_from_admin_panel_to_pos()
    {
        $kasir = User::factory()->create([
            'email' => 'kasir@raabiha.com',
        ]);
        $kasir->assignRole('kasir');

        $response = $this->actingAs($kasir)->get('/admin');

        $response->assertRedirect('/pos');
    }

    public function test_kasir_user_can_access_pos_directly()
    {
        $kasir = User::factory()->create([
            'email' => 'kasir@raabiha.com',
        ]);
        $kasir->assignRole('kasir');

        $response = $this->actingAs($kasir)->get('/pos');

        $response->assertStatus(200);
    }

    public function test_super_admin_is_not_redirected_from_admin_panel_to_pos()
    {
        $admin = User::factory()->create([
            'email' => 'admin@raabiha.com',
        ]);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/admin');

        $this->assertStringNotContainsString('/pos', $response->headers->get('Location') ?? '');
    }

    public function test_unauthenticated_user_accessing_pos_is_redirected_to_pos_login()
    {
        $response = $this->get('/pos');
        $response->assertRedirect(route('pos.login'));
    }

    public function test_kasir_can_login_via_pos_login_page()
    {
        $kasir = User::factory()->create([
            'email' => 'kasir1@raabiha.com',
            'password' => bcrypt('password123'),
        ]);
        $kasir->assignRole('kasir');

        \Livewire\Livewire::test(\App\Livewire\Auth\PosLogin::class)
            ->set('loginInput', 'kasir1@raabiha.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('pos.index'));

        $this->assertAuthenticatedAs($kasir);
    }

    public function test_customer_without_pos_role_cannot_login_via_pos_login_page()
    {
        $customer = User::factory()->create([
            'email' => 'customer@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        \Livewire\Livewire::test(\App\Livewire\Auth\PosLogin::class)
            ->set('loginInput', 'customer@gmail.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('loginInput');

        $this->assertGuest();
    }
}
