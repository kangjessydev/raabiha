<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BinderByteService;

class PublicOrderTrack extends Component
{
    public $awb = '';
    public $courier = 'jnt';
    public $trackingInfo = null;
    public $trackingError = null;
    public $trackingLoading = false;

    protected $rules = [
        'awb' => 'required|string|min:5',
        'courier' => 'required|string',
    ];

    public function track()
    {
        $this->validate();

        $this->trackingLoading = true;
        $this->trackingError = null;
        $this->trackingInfo = null;

        try {
            $result = BinderByteService::trackPackage($this->courier, $this->awb);

            if ($result['success']) {
                $this->trackingInfo = $result['data'];
            } else {
                $errorMsg = $result['message'] ?? 'Informasi pelacakan tidak ditemukan.';
                if (strtolower($errorMsg) === 'data not found' || stripos($errorMsg, 'not found') !== false) {
                    $errorMsg = 'Nomor resi tidak ditemukan atau belum terupdate oleh pihak kurir. Harap tunggu beberapa jam setelah paket diserahkan ke kurir.';
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
        return view('livewire.public-order-track');
    }
}
