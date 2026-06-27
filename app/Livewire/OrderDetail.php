<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    public $order;
    public $trackingInfo = null;
    public $trackingLoading = false;
    public $trackingError = null;

    public function mount()
    {
        $orderId = request()->query('id');
        $this->order = \App\Models\Order::with(['items.product', 'items.variant.attributeOptions.attribute'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);
    }

    public function trackPackage()
    {
        if (!$this->order->awb_number || !$this->order->courier) {
            return;
        }

        $this->trackingLoading = true;
        $this->trackingError = null;
        $this->trackingInfo = null;

        try {
            // Extract clean courier code (e.g., 'jne' from 'jne_reg' or 'jne|REG|20000')
            $courierRaw = $this->order->courier;
            if (str_contains($courierRaw, '|')) {
                $courierRaw = explode('|', $courierRaw)[0];
            }
            $courier = explode('_', strtolower($courierRaw))[0];
            
            $result = \App\Services\BinderByteService::trackPackage($courier, $this->order->awb_number);

            if ($result['success']) {
                $this->trackingInfo = $result['data'];
            } else {
                $this->trackingError = $result['message'] ?? 'Informasi pelacakan tidak ditemukan.';
            }
        } catch (\Exception $e) {
            $this->trackingError = 'Gagal memuat status pelacakan: ' . $e->getMessage();
        } finally {
            $this->trackingLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.order-detail')->layout('components.layouts.app', [
            'title' => 'Invoice Pesanan #' . $this->order->order_number,
            'robots' => 'noindex, nofollow'
        ]);
    }
}
