<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PosSession;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAutoCloseOverdueShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_is_auto_closed_when_overtime_exceeded()
    {
        \Spatie\Permission\Models\Role::create(['name' => 'super_admin']);

        // 1. Buat user admin
        $admin = User::factory()->create([
            'name'  => 'Super Admin Test',
            'email' => 'admin_test@raabiha.com',
        ]);
        $admin->assignRole('super_admin');

        // 2. Buat master shift: Shift Dini Hari (01:00 - 03:00 AM)
        $masterShifts = [
            [
                'shift_name' => 'Shift Dini Hari',
                'start_time' => '01:00',
                'end_time'   => '03:00',
                'assigned_cashiers' => [],
            ]
        ];

        // 3. Daftarkan admin di Whitelist dan ikat ke Shift Dini Hari
        $whitelistUsers = [
            [
                'user_id'    => (string)$admin->id,
                'shift_name' => 'Shift Dini Hari',
            ]
        ];

        SiteSetting::create(['key' => 'pos_master_shifts', 'value' => json_encode($masterShifts)]);
        SiteSetting::create(['key' => 'pos_whitelist_users', 'value' => json_encode($whitelistUsers)]);
        SiteSetting::create(['key' => 'pos_allowed_user_ids', 'value' => json_encode([$admin->id])]);
        SiteSetting::create(['key' => 'pos_shift_restriction_enabled', 'value' => 'true']);
        SiteSetting::create(['key' => 'pos_shift_overtime_max_hours', 'value' => '2']);

        // 4. Buat sesi shift hari ini yang dibuka jam 01:00 AM (saat ini jam 08:24 AM, Selesai 03:00 AM + Lembur Max 2 jam = Cutoff 05:00 AM)
        $openSession = PosSession::create([
            'cashier_id'   => $admin->id,
            'opened_at'    => now()->startOfDay()->addHour(1),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $this->assertEquals('open', $openSession->status);

        // 5. Jalankan autoCloseStaleSessions()
        PosSession::autoCloseStaleSessions();

        // 6. Pastikan shift berubah status menjadi closed dengan catatan lembur terlampaui
        $openSession->refresh();
        $this->assertEquals('closed', $openSession->status);
        $this->assertStringContainsString('Batas Jam Kerja & Lembur', $openSession->notes);
    }

    public function test_whitelisted_user_without_assigned_shift_is_unrestricted()
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $whitelistUsers = [
            [
                'user_id'    => (string)$admin->id,
                'shift_name' => null, // Bebas 24 jam
            ]
        ];

        SiteSetting::create(['key' => 'pos_whitelist_users', 'value' => json_encode($whitelistUsers)]);
        SiteSetting::create(['key' => 'pos_allowed_user_ids', 'value' => json_encode([$admin->id])]);
        SiteSetting::create(['key' => 'pos_shift_restriction_enabled', 'value' => 'true']);

        $component = \Livewire\Livewire::test(\App\Livewire\PosManager::class);
        $result = $component->instance()->isCurrentShiftAllowed($admin->id);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('Whitelist Unrestricted Access', $result['reason']);
    }
}
