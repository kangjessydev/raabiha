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
}
