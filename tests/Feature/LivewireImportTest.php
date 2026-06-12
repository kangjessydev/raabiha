<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireImportTest extends TestCase
{
    public function test_can_download_sample_csv()
    {
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }
        $this->actingAs($user);

        Livewire::test(ListProducts::class)
            ->callAction('import', 'downloadExample');
    }
}
