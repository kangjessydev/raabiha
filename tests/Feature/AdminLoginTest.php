<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Pages\Auth\Login;
use Spatie\Permission\Models\Role;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders_default_filament_view(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Username / Email');
        $response->assertSee('Masuk');
    }

    public function test_admin_can_login_with_username_or_email(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'email' => 'admin@raabiha.com',
            'password' => bcrypt('secret123'),
        ]);
        $admin->assignRole('super_admin');

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin',
                'password' => 'secret123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    }
}
