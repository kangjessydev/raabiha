<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    public $order;

    public function mount()
    {
        $orderId = request()->query('id');
        $this->order = \App\Models\Order::with(['items.product', 'items.variant.attributeOptions.attribute'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);
    }

    public function render()
    {
        return view('livewire.order-detail')->layout('components.layouts.app', [
            'title' => 'Invoice Pesanan #' . $this->order->order_number,
            'robots' => 'noindex, nofollow'
        ]);
    }
}
