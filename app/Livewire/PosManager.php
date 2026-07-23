<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\PosSession;
use App\Services\PosTransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $this->activeSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();
    }

    public function openSession()
    {
        $this->validate([
            'openingCash' => 'required|numeric|min:0',
        ]);

        PosSession::create([
            'cashier_id' => Auth::id(),
            'opened_at'  => now(),
            'opening_cash' => $this->openingCash,
            'status'     => 'open',
        ]);

        $this->loadActiveSession();
        $this->dispatch('session-opened');
    }

    public function closeSession()
    {
        if (!$this->activeSession) return;

        $this->validate([
            'actualEndingCash' => 'required|numeric|min:0',
        ]);

        $totalSales    = $this->activeSession->orders()->sum('cash_paid')
                       - $this->activeSession->orders()->sum('cash_change');
        $expectedEnding = $this->activeSession->opening_cash + $totalSales;

        $this->activeSession->update([
            'closed_at'            => now(),
            'expected_ending_cash' => $expectedEnding,
            'actual_ending_cash'   => $this->actualEndingCash,
            'difference_cash'      => $this->actualEndingCash - $expectedEnding,
            'status'               => 'closed',
            'notes'                => $this->sessionNotes,
        ]);

        $escPosService = new \App\Services\EscPosService();
        $zReportBase64 = $escPosService->generateZReport($this->activeSession);

        $this->activeSession    = null;
        $this->openingCash      = 0;
        $this->actualEndingCash = 0;
        $this->sessionNotes     = '';

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

        $data = is_string($payload) ? json_decode($payload, true) : $payload;
        $data['cashier_id']    = Auth::id();
        $data['pos_session_id'] = $this->activeSession->id;

        try {
            $service = new PosTransactionService();
            $order   = $service->completePosTransaction($data);

            $escPosService  = new \App\Services\EscPosService();
            $receiptBase64  = $escPosService->generateReceipt($order);

            $this->dispatch('checkout-success', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'grand_total'  => $order->grand_total,
                'cash_change'  => $order->cash_change,
            ]);
            $this->dispatch('print-receipt', ['base64' => $receiptBase64]);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $products         = [];
        $sessionOrders    = collect();
        $sessionCustomers = collect();
        $sessionStats     = [];

        if ($this->activeSession) {
            /* ---------- Product list ---------- */
            $query = Product::with(['variants'])
                ->where('is_active', true)
                ->whereIn('channel_visibility', ['pos_only', 'both']);

            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku',  'like', '%' . $this->search . '%');
                });

                $products = $query->limit(48)->get()->map(function ($p) {
                    $p->computed_stock = $p->has_variants ? $p->variants->sum('stock') : $p->stock;
                    return $p;
                });
            } else {
                $top3 = (clone $query)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->where('sold_count', '>=', 5)
                    ->having('computed_stock', '>', 0)
                    ->orderBy('sold_count', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($p) {
                        $p->is_best_seller = true;
                        return $p;
                    });

                $top3Ids = $top3->pluck('id')->toArray();

                $remaining = (clone $query)
                    ->whereNotIn('id', $top3Ids)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->orderByRaw('computed_stock > 0 DESC')
                    ->orderBy('computed_stock', 'desc')
                    ->limit(45)
                    ->get();

                $products = $top3->concat($remaining);
            }

            /* ---------- Riwayat Transaksi (shift ini) ---------- */
            $sessionOrders = Order::with('items')
                ->where('pos_session_id', $this->activeSession->id)
                ->latest()
                ->limit(50)
                ->get();

            /* ---------- Pelanggan (shift ini, unik by nama) ---------- */
            $sessionCustomers = Order::where('pos_session_id', $this->activeSession->id)
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->select(
                    'customer_name',
                    'customer_phone',
                    DB::raw('COUNT(*) as visit_count'),
                    DB::raw('SUM(grand_total) as total_spent'),
                    DB::raw('MAX(created_at) as last_visit')
                )
                ->groupBy('customer_name', 'customer_phone')
                ->orderByDesc('total_spent')
                ->get();

            /* ---------- Rekap Kas ---------- */
            $cashSales    = $sessionOrders->where('payment_method', 'cash')->sum('grand_total');
            $nonCashSales = $sessionOrders->where('payment_method', '!=', 'cash')->sum('grand_total');

            $sessionStats = [
                'total_trx'      => $sessionOrders->count(),
                'cash_sales'     => $cashSales,
                'non_cash_sales' => $nonCashSales,
                'total_sales'    => $cashSales + $nonCashSales,
                'opening_cash'   => $this->activeSession->opening_cash,
                'expected_cash'  => $this->activeSession->opening_cash + $cashSales,
                'opened_at'      => $this->activeSession->opened_at->format('d M Y, H:i'),
            ];
        }

        $vouchers = \App\Models\Voucher::whereIn('usable_channel', ['pos_only', 'both'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)
            ->get()
            ->filter(function ($method) {
                $config = is_array($method->config) ? $method->config : (json_decode($method->config ?? '[]', true) ?? []);
                $availability = $config['availability'] ?? 'both';
                return in_array($availability, ['both', 'offline']);
            })
            ->map(function ($method) {
                return [
                    'id'      => $method->id,
                    'name'    => $method->name,
                    'code'    => $method->code,
                    'logo'    => $method->logo ? asset('storage/' . $method->logo) : null,
                    'is_cash' => strtolower($method->code) === 'tunai' || strtolower($method->name) === 'tunai',
                ];
            })
            ->values();

        return view('livewire.pos-manager', [
            'products'         => $products,
            'vouchers'         => $vouchers,
            'paymentMethods'   => $paymentMethods,
            'sessionOrders'    => $sessionOrders,
            'sessionCustomers' => $sessionCustomers,
            'sessionStats'     => $sessionStats,
        ]);
    }
}
