<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SiteSetting;

class ResellerWelcome extends Component
{
    public $fee;
    public $discount;
    public $terms;
    public $banks = [];
    public $whatsapp = '';

    public function mount()
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->to('/reseller-register');
        }

        $settings = SiteSetting::whereIn('key', [
            'reseller_min_deposit',
            'reseller_discount_percent',
            'reseller_terms',
            'reseller_banks',
            'reseller_whatsapp_payment'
        ])->pluck('value', 'key')->toArray();

        $this->fee = $settings['reseller_min_deposit'] ?? 0;
        $this->discount = $settings['reseller_discount_percent'] ?? 0;
        $this->terms = $settings['reseller_terms'] ?? '';
        
        $banksData = $settings['reseller_banks'] ?? '[]';
        $this->banks = json_decode($banksData, true) ?? [];
        $this->whatsapp = $settings['reseller_whatsapp_payment'] ?? '';
    }

    public function render()
    {
        return view('livewire.reseller-welcome')->layout('components.layouts.app', [
            'title' => 'Selamat Datang Calon Reseller'
        ]);
    }
}
