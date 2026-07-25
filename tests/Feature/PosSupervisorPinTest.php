<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\PosManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
