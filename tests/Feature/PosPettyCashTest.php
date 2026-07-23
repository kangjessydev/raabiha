<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\PosSession;
use App\Models\User;
use App\Livewire\PosManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPettyCashTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_record_petty_cash_in_and_out()
    {
        $cashier = User::factory()->create();
        $this->actingAs($cashier);

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        // Record Kas Keluar (Pengeluaran galon air)
        Livewire::test(PosManager::class)
            ->set('pettyCashType', 'out')
            ->set('pettyCashAmount', 25000)
            ->set('pettyCashNotes', 'Beli isi ulang air galon ruko')
            ->call('recordPettyCash')
            ->assertDispatched('petty-cash-saved');

        $this->assertDatabaseHas('cashflows', [
            'source' => 'pos',
            'category' => 'pos_petty_cash',
            'type' => 'out',
            'amount' => 25000,
            'description' => 'Kas Keluar POS: Beli isi ulang air galon ruko',
        ]);

        // Record Kas Masuk (Tambah modal koin)
        Livewire::test(PosManager::class)
            ->set('pettyCashType', 'in')
            ->set('pettyCashAmount', 10000)
            ->set('pettyCashNotes', 'Tambah uang koin kembalian')
            ->call('recordPettyCash')
            ->assertDispatched('petty-cash-saved');

        $this->assertDatabaseHas('cashflows', [
            'source' => 'pos',
            'category' => 'pos_petty_cash',
            'type' => 'in',
            'amount' => 10000,
            'description' => 'Kas Masuk POS: Tambah uang koin kembalian',
        ]);

        // Test Close Session recalculation: opening (100k) + 0 sales + 10k in - 25k out = 85k
        Livewire::test(PosManager::class)
            ->set('actualEndingCash', 85000)
            ->call('closeSession')
            ->assertDispatched('session-closed');

        $session->refresh();
        $this->assertEquals(85000, $session->expected_ending_cash);
        $this->assertEquals(0, $session->difference_cash);
        $this->assertEquals('closed', $session->status);
    }
}
