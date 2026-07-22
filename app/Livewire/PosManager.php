<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\PosSession;
use App\Services\PosTransactionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.pos')]
class PosManager extends Component
{
    public $search = '';
    public $activeSession = null;
    
    // State untuk form buka/tutup shift
    public $openingCash = 0;
    public $actualEndingCash = 0;
    public $sessionNotes = '';

    public function mount()
    {
        $this->loadActiveSession();
    }

    public function loadActiveSession()
    {
        // Cari sesi yang masih open untuk user yang sedang login
        $this->activeSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();
    }

    public function openSession()
    {
        $this->validate([
            'openingCash' => 'required|numeric|min:0'
        ]);

        PosSession::create([
            'cashier_id' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $this->openingCash,
            'status' => 'open',
        ]);

        $this->loadActiveSession();
        $this->dispatch('session-opened');
    }

    public function closeSession()
    {
        if (!$this->activeSession) return;

        $this->validate([
            'actualEndingCash' => 'required|numeric|min:0'
        ]);

        // Hitung total cash dari pesanan di sesi ini
        $totalSales = $this->activeSession->orders()->sum('cash_paid') - $this->activeSession->orders()->sum('cash_change');
        $expectedEnding = $this->activeSession->opening_cash + $totalSales;
        
        $this->activeSession->update([
            'closed_at' => now(),
            'expected_ending_cash' => $expectedEnding,
            'actual_ending_cash' => $this->actualEndingCash,
            'difference_cash' => $this->actualEndingCash - $expectedEnding,
            'status' => 'closed',
            'notes' => $this->sessionNotes,
        ]);

        $escPosService = new \App\Services\EscPosService();
        $zReportBase64 = $escPosService->generateZReport($this->activeSession);

        $this->activeSession = null;
        $this->openingCash = 0;
        $this->actualEndingCash = 0;
        $this->sessionNotes = '';
        $this->dispatch('session-closed');
        $this->dispatch('print-z-report', ['base64' => $zReportBase64]);
    }

    /**
     * Menerima payload checkout dari Alpine.js
     */
    public function processCheckout($payload)
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi kasir belum dibuka!']);
            return;
        }

        // Dekode dari JSON array
        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        // Validasi idempotency di sisi Livewire (Opsional, tapi bagus kalau ada state)
        // Di sini kita percayakan pada PosTransactionService
        $data['cashier_id'] = Auth::id();
        $data['pos_session_id'] = $this->activeSession->id;

        try {
            $service = new PosTransactionService();
            $order = $service->completePosTransaction($data);

            $escPosService = new \App\Services\EscPosService();
            $receiptBase64 = $escPosService->generateReceipt($order);

            // Berhasil
            $this->dispatch('checkout-success', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
                'cash_change' => $order->cash_change,
            ]);
            
            $this->dispatch('print-receipt', ['base64' => $receiptBase64]);

        } catch (\Exception $e) {
            // Gagal (stok habis, dsb)
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        // Ambil produk yang bisa dijual di POS
        $products = [];
        if ($this->activeSession) {
            $query = Product::with(['variants'])
                ->where('is_active', true)
                ->whereIn('channel_visibility', ['pos_only', 'both']);
                
            if (!empty($this->search)) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
                
                // Fetch search results
                $products = $query->limit(48)->get()->map(function($p) {
                    $p->computed_stock = $p->has_variants ? $p->variants->sum('stock') : $p->stock;
                    return $p;
                });
            } else {
                // Ambil 3 produk terlaris yang masih ada stok
                $top3 = (clone $query)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->having('computed_stock', '>', 0)
                    ->orderBy('sold_count', 'desc')
                    ->limit(3)
                    ->get();
                
                $top3Ids = $top3->pluck('id')->toArray();
                
                // Ambil sisanya, urutkan berdasarkan stok terbanyak (stok > 0 dulu, baru = 0)
                $remaining = (clone $query)
                    ->whereNotIn('id', $top3Ids)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->orderByRaw('computed_stock > 0 DESC')
                    ->orderBy('computed_stock', 'desc')
                    ->limit(45) // Total 48 produk
                    ->get();
                    
                $products = $top3->concat($remaining);
            }
        }

        return view('livewire.pos-manager', [
            'products' => $products
        ]);
    }
}
