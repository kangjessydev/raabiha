<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\PosManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosSupervisorPinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
    }

    public function test_supervisor_pin_verification()
    {
        $owner = User::factory()->create([
            'pos_pin' => Hash::make('888888'),
        ]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create([
            'pos_pin' => Hash::make('111111'),
        ]);
        $cashier->assignRole('kasir');

        // Wrong PIN -> dispatches supervisor-auth-failed
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '000000', 'manual_discount')
            ->assertDispatched('supervisor-auth-failed');

        // Cashier's PIN -> fails as cashier is not selected supervisor
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '111111', 'manual_discount')
            ->assertDispatched('supervisor-auth-failed');

        // Owner's Supervisor PIN -> dispatches supervisor-authorized
        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '888888', 'manual_discount')
            ->assertDispatched('supervisor-authorized');
    }

    public function test_supervisor_must_verify_pin_when_authorizing_self()
    {
        $owner = User::factory()->create([
            'pos_pin' => Hash::make('888888'),
        ]);
        $owner->assignRole('owner');

        // Even when logged in as owner, wrong PIN fails
        Livewire::actingAs($owner)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '123456', 'manual_discount')
            ->assertDispatched('supervisor-auth-failed');

        // Correct PIN succeeds
        Livewire::actingAs($owner)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '888888', 'manual_discount')
            ->assertDispatched('supervisor-authorized');
    }

    public function test_rate_limiting_is_per_supervisor_not_per_cashier()
    {
        Cache::flush();

        $owner = User::factory()->create([
            'pos_pin' => Hash::make('888888'),
        ]);
        $owner->assignRole('owner');

        $cashier1 = User::factory()->create(['pos_pin' => Hash::make('111111')]);
        $cashier1->assignRole('kasir');
        $cashier2 = User::factory()->create(['pos_pin' => Hash::make('222222')]);
        $cashier2->assignRole('kasir');

        // Kasir 1 salah 3x -> supervisor X terkunci
        for ($i = 0; $i < 3; $i++) {
            Livewire::actingAs($cashier1)
                ->test(PosManager::class)
                ->call('verifySupervisorPin', $owner->id, '000000', 'manual_discount')
                ->assertDispatched('supervisor-auth-failed');
        }

        // Kasir 2 mencoba supervisor yang sama -> harus kena lockout juga (meski PIN benar)
        Livewire::actingAs($cashier2)
            ->test(PosManager::class)
            ->call('verifySupervisorPin', $owner->id, '888888', 'manual_discount')
            ->assertDispatched('supervisor-auth-failed');

        // Verifikasi: cache key berbasis supervisor ID, bukan cashier ID
        $this->assertTrue(Cache::has('sup_pin_lock_sup_' . $owner->id));
        $this->assertFalse(Cache::has('sup_pin_lock_sup_' . $cashier1->id));
    }
}
