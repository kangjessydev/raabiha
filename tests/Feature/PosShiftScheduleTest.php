<?php

namespace Tests\Feature;

use App\Models\PosSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosShiftScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
    }

    public function test_cashier_can_have_pos_shift_hours()
    {
        $cashier = User::factory()->create([
            'name'            => 'Kasir Pagi',
            'pos_shift_start' => '08:00:00',
            'pos_shift_end'   => '16:00:00',
        ]);

        $this->assertEquals('08:00:00', $cashier->pos_shift_start);
        $this->assertEquals('16:00:00', $cashier->pos_shift_end);
    }

    public function test_stale_session_from_yesterday_is_auto_closed_on_safety_net()
    {
        $cashier = User::factory()->create();

        // Create open session from yesterday
        $staleSession = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now()->subDays(2)->setHour(8)->setMinute(0),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        // Auto close stale sessions
        PosSession::autoCloseStaleSessions($cashier->id);

        $staleSession->refresh();
        $this->assertEquals('closed', $staleSession->status);
        $this->assertNotNull($staleSession->closed_at);
        $this->assertStringContainsString('Otomatis ditutup oleh sistem', $staleSession->notes);
    }

    public function test_pos_manager_auto_cleans_stale_session_and_allows_fresh_opening()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        // Create stale open session from yesterday
        $staleSession = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now()->subDay()->setHour(9)->setMinute(0),
            'opening_cash' => 150000,
            'status'       => 'open',
        ]);

        \Livewire\Livewire::actingAs($cashier)
            ->test(\App\Livewire\PosManager::class)
            ->call('loadActiveSession')
            ->assertSet('activeSession', null);

        $staleSession->refresh();
        $response = $this->actingAs($cashier)->get('/pos');
        $response->assertStatus(200);
    }

    public function test_pos_session_resource_table_renders_with_null_closed_at()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $session = PosSession::create([
            'cashier_id'   => $admin->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
            'closed_at'    => null,
        ]);

        $this->actingAs($admin)
            ->get('/admin/pos-sessions')
            ->assertStatus(200)
            ->assertSee('Shift Aktif');
    }
}
