<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class OrderTrack extends Component
{
    public $order;
    public $trackingInfo = null;
    public $trackingLoading = true;
    public $trackingError = null;

    public function mount($id)
    {
        $this->order = \App\Models\Order::with(['items.product'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
            
        // Start tracking automatically on page load
        $this->trackPackage();
    }

    public function trackPackage()
    {
        if (!$this->order->awb_number || !$this->order->courier) {
            $this->trackingError = 'Tidak ada nomor resi atau informasi kurir untuk pesanan ini.';
            $this->trackingLoading = false;
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
                $errorMsg = $result['message'] ?? 'Informasi pelacakan tidak ditemukan.';
                
                // Translate common API errors
                if (strtolower($errorMsg) === 'data not found') {
                    $errorMsg = 'Resi tidak ditemukan atau belum terdaftar. Harap tunggu 1x24 jam setelah pengiriman.';
                } elseif (stripos($errorMsg, 'bad request') !== false) {
                    $errorMsg = 'Permintaan tidak valid, mohon periksa kembali nomor resi Anda.';
                }
                
                $this->trackingError = $errorMsg;
            }
        } catch (\Exception $e) {
            $this->trackingError = 'Gagal memuat status pelacakan: ' . $e->getMessage();
        } finally {
            $this->trackingLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.order-track')->layout('components.layouts.app', [
            'title' => 'Lacak Pesanan #' . $this->order->order_number,
            'robots' => 'noindex, nofollow'
        ]);
    }
}
