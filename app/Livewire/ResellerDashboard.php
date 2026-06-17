<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class ResellerDashboard extends Component
{
    public $activeTab = 'overview'; // overview, pesanan, komisi, diskon

    public function mount()
    {
        if (!Auth::check() || Auth::user()->role !== 'reseller') {
            return redirect()->to('/reseller-register');
        }
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/login');
    }

    public function getOrdersProperty()
    {
        return Order::with(['items.product', 'items.variant.attributeOptions.attribute'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.reseller-dashboard')->layout('components.layouts.app', [
            'title' => 'Portal Reseller'
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24 animate-pulse">
            <div class="mb-12 hidden md:block">
                <div class="h-12 bg-[#e5e2de] w-1/4 mb-4"></div>
                <div class="h-4 bg-[#e5e2de] w-1/6"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] lg:grid-cols-[280px_1fr] gap-10 lg:gap-16 items-start">
                <aside class="flex flex-col gap-4">
                    <div class="h-10 bg-[#e5e2de] w-full"></div>
                    <div class="h-10 bg-[#e5e2de] w-full"></div>
                    <div class="h-10 bg-[#e5e2de] w-full"></div>
                </aside>
                <div class="flex flex-col gap-8 w-full">
                    <div class="h-32 bg-[#e5e2de] w-full"></div>
                    <div class="h-48 bg-[#e5e2de] w-full"></div>
                </div>
            </div>
        </div>
        HTML;
    }
}
