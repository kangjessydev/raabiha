<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\User;
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

    // State Petty Cash
    public $pettyCashType = 'out';
    public $pettyCashAmount = 0;
    public $pettyCashNotes = '';

    // State PIN POS 6-Digit
    public $hasPosPin = false;
    public $posPinInput = '';
    public $posPinConfirm = '';
    public $oldPosPin = '';
    public $newPosPin = '';
    public $newPosPinConfirm = '';

    public function recordPettyCash()
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi shift kasir belum dibuka!']);
            return;
        }

        $this->validate([
            'pettyCashType'   => 'required|in:in,out',
            'pettyCashAmount' => 'required|numeric|gt:0',
            'pettyCashNotes'  => 'required|string|max:255',
        ], [
            'pettyCashAmount.gt' => 'Nominal petty cash harus lebih besar dari Rp 0.',
            'pettyCashNotes.required' => 'Keterangan pengeluaran/pemasukan kas wajib diisi.',
        ]);

        \App\Models\Cashflow::create([
            'transaction_date' => now()->toDateString(),
            'type'             => $this->pettyCashType,
            'category'         => 'pos_petty_cash',
            'amount'           => $this->pettyCashAmount,
            'description'      => ($this->pettyCashType === 'out' ? 'Kas Keluar POS: ' : 'Kas Masuk POS: ') . $this->pettyCashNotes,
            'order_id'         => null,
            'source'           => 'pos',
            'is_reversed'      => false,
        ]);

        $label = $this->pettyCashType === 'out' ? 'Kas Keluar' : 'Kas Masuk';
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$label} senilai Rp " . number_format($this->pettyCashAmount, 0, ',', '.') . " berhasil dicatat."
        ]);
        $this->dispatch('petty-cash-saved');

        $this->pettyCashAmount = 0;
        $this->pettyCashNotes = '';
        $this->pettyCashType = 'out';
    }

    public function mount()
    {
        $this->loadActiveSession();
        $this->hasPosPin = !empty(Auth::user()->pos_pin);
    }

    public function loadActiveSession()
    {
        $this->activeSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();
    }

    public function openSession()
    {
        if ($this->activeSession) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Anda masih memiliki sesi kasir yang aktif. Tutup shift terlebih dahulu.',
            ]);
            return;
        }

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

        $pettyCashIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'in')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->sum('amount');

        $pettyCashOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'out')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->sum('amount');

        $totalCashSales = $this->activeSession->orders()
            ->where('payment_method', 'cash')
            ->sum('grand_total');

        $expectedEnding = $this->activeSession->opening_cash + $totalCashSales + $pettyCashIn - $pettyCashOut;

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

    public function reprintReceipt($orderId)
    {
        $order = Order::with(['items', 'cashier'])->find($orderId);
        if (!$order) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
            return;
        }

        try {
            $escPosService = new \App\Services\EscPosService();
            $receiptBase64 = $escPosService->generateReceipt($order, isReprint: true);

            $this->dispatch('print-receipt', ['base64' => $receiptBase64]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Cetak ulang struk #' . $order->order_number . ' dikirim ke printer.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mencetak ulang struk: ' . $e->getMessage()]);
        }
    }

    public function saveInitialPosPin()
    {
        $this->validate([
            'posPinInput'   => 'required|digits:6',
            'posPinConfirm' => 'required|same:posPinInput',
        ], [
            'posPinInput.required'   => 'PIN POS wajib diisi.',
            'posPinInput.digits'     => 'PIN POS harus berupa 6 digit angka.',
            'posPinConfirm.required' => 'Konfirmasi PIN wajib diisi.',
            'posPinConfirm.same'     => 'Konfirmasi PIN tidak cocok.',
        ]);

        Auth::user()->update([
            'pos_pin' => \Illuminate\Support\Facades\Hash::make($this->posPinInput),
        ]);

        $this->hasPosPin = true;
        $this->posPinInput = '';
        $this->posPinConfirm = '';

        $this->dispatch('pin-created');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'PIN POS 6-digit berhasil dibuat!']);
    }

    public function unlockScreenWithPin($pin)
    {
        $user = Auth::user();
        if (empty($user->pos_pin)) {
            $this->dispatch('screen-unlock-failed', ['message' => 'Anda belum membuat PIN POS. Silakan buat PIN terlebih dahulu.']);
            return;
        }

        if (\Illuminate\Support\Facades\Hash::check($pin, $user->pos_pin)) {
            $this->dispatch('screen-unlocked');
        } else {
            $this->dispatch('screen-unlock-failed', ['message' => 'PIN POS 6-digit salah!']);
        }
    }

    public function changePosPin()
    {
        $user = Auth::user();
        if (empty($user->pos_pin)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Anda belum memiliki PIN POS.']);
            return;
        }

        $this->validate([
            'oldPosPin'        => 'required|digits:6',
            'newPosPin'        => 'required|digits:6|different:oldPosPin',
            'newPosPinConfirm' => 'required|same:newPosPin',
        ], [
            'oldPosPin.required'        => 'PIN lama wajib diisi.',
            'oldPosPin.digits'          => 'PIN lama harus 6 digit angka.',
            'newPosPin.required'        => 'PIN baru wajib diisi.',
            'newPosPin.digits'          => 'PIN baru harus 6 digit angka.',
            'newPosPin.different'       => 'PIN baru harus berbeda dengan PIN lama.',
            'newPosPinConfirm.required' => 'Konfirmasi PIN baru wajib diisi.',
            'newPosPinConfirm.same'     => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($this->oldPosPin, $user->pos_pin)) {
            $this->addError('oldPosPin', 'PIN lama Anda tidak sesuai.');
            return;
        }

        $user->update([
            'pos_pin' => \Illuminate\Support\Facades\Hash::make($this->newPosPin),
        ]);

        $this->oldPosPin = '';
        $this->newPosPin = '';
        $this->newPosPinConfirm = '';

        $this->dispatch('pin-changed');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'PIN POS 6-digit berhasil diperbarui.']);
    }

    public function verifySupervisorPin($supervisorId, $pin, $actionType = '')
    {
        $lockKey = 'sup_pin_lock_' . Auth::id();
        $attemptsKey = 'sup_pin_attempts_' . Auth::id();

        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            $seconds = \Illuminate\Support\Facades\Cache::get($lockKey) - time();
            $this->dispatch('supervisor-auth-failed', ['message' => 'Terlalu banyak percobaan PIN salah. Coba lagi dalam ' . max(1, $seconds) . ' detik.']);
            return;
        }

        $supervisor = User::find($supervisorId);
        $isSupRole = $supervisor && ($supervisor->hasAnyRole(['super_admin', 'owner', 'manager', 'finance']) || in_array($supervisor->role, ['super_admin', 'owner', 'manager', 'finance']));

        if ($supervisor && $isSupRole && $supervisor->pos_pin && \Illuminate\Support\Facades\Hash::check($pin, $supervisor->pos_pin)) {
            \Illuminate\Support\Facades\Cache::forget($attemptsKey);
            $this->dispatch('supervisor-authorized', ['actionType' => $actionType, 'supervisorName' => $supervisor->name]);
        } else {
            $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts, 300);

            if ($attempts >= 3) {
                \Illuminate\Support\Facades\Cache::put($lockKey, time() + 60, 60);
                \Illuminate\Support\Facades\Cache::forget($attemptsKey);
                $this->dispatch('supervisor-auth-failed', ['message' => 'PIN Supervisor salah 3x. Akses terkunci selama 60 detik demi keamanan.']);
            } else {
                $remaining = 3 - $attempts;
                $this->dispatch('supervisor-auth-failed', ['message' => 'PIN Supervisor ' . ($supervisor ? $supervisor->name : '') . ' salah! Sisa percobaan: ' . $remaining . 'x.']);
            }
        }
    }

    public function voidOrder($orderId, $supervisorId, $supervisorPin, $reason = '')
    {
        $lockKey = 'sup_pin_lock_' . Auth::id();
        $attemptsKey = 'sup_pin_attempts_' . Auth::id();

        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            $seconds = \Illuminate\Support\Facades\Cache::get($lockKey) - time();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Terlalu banyak percobaan PIN salah. Coba lagi dalam ' . max(1, $seconds) . ' detik.']);
            return;
        }

        $order = Order::with(['items', 'voidBy'])->find($orderId);
        if (!$order) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
            return;
        }

        if ($order->status === 'cancelled') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi ini sudah pernah dibatalkan (Void).']);
            return;
        }

        $supervisor = User::find($supervisorId);
        $isSupervisorRole = $supervisor && ($supervisor->hasAnyRole(['super_admin', 'owner', 'manager', 'finance']) || in_array($supervisor->role, ['super_admin', 'owner', 'manager', 'finance']));
        $isValidPin = $supervisor && $supervisor->pos_pin && \Illuminate\Support\Facades\Hash::check($supervisorPin, $supervisor->pos_pin);

        if (!$supervisor || !$isSupervisorRole || !$isValidPin) {
            $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts, 300);

            if ($attempts >= 3) {
                \Illuminate\Support\Facades\Cache::put($lockKey, time() + 60, 60);
                \Illuminate\Support\Facades\Cache::forget($attemptsKey);
                $this->dispatch('notify', ['type' => 'error', 'message' => 'PIN Supervisor salah 3x. Akses terkunci selama 60 detik demi keamanan.']);
            } else {
                $remaining = 3 - $attempts;
                $this->dispatch('notify', ['type' => 'error', 'message' => 'PIN Supervisor ' . ($supervisor ? $supervisor->name : '') . ' salah! Sisa percobaan: ' . $remaining . 'x.']);
            }
            return;
        }

        \Illuminate\Support\Facades\Cache::forget($attemptsKey);

        try {
            DB::transaction(function () use ($order, $supervisor, $reason) {
                // 1. Mark order as cancelled/void
                $order->update([
                    'status'         => 'cancelled',
                    'payment_status' => 'failed',
                    'void_by_id'     => $supervisor->id,
                    'void_reason'    => $reason ?: 'Void Kasir POS',
                    'void_at'        => now(),
                ]);

                // 2. Restock product items
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $variant->increment('stock', $item->quantity);
                        }
                    } else {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $item->quantity);
                        }
                    }

                    // Stock log
                    if (class_exists(\App\Models\StockLog::class)) {
                        $currentStock = $item->product_variant_id
                            ? (\App\Models\ProductVariant::find($item->product_variant_id)->stock ?? 0)
                            : (\App\Models\Product::find($item->product_id)->stock ?? 0);

                        \App\Models\StockLog::create([
                            'product_id'         => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'user_id'            => Auth::id(),
                            'type'               => 'in',
                            'quantity_before'    => max(0, $currentStock - $item->quantity),
                            'quantity_change'    => $item->quantity,
                            'quantity_after'     => $currentStock,
                            'reason'             => 'pos_void',
                            'notes'              => 'Restock Void Nota POS #' . $order->order_number . ' (Disetujui Supervisor: ' . $supervisor->name . ')',
                        ]);
                    }
                }

                // 3. Record Cashflow Reversal
                if ($order->payment_method === 'cash') {
                    \App\Models\Cashflow::create([
                        'transaction_date' => now()->toDateString(),
                        'type'             => 'out',
                        'category'         => 'pos_void',
                        'amount'           => $order->grand_total,
                        'description'      => 'Void Transaksi POS #' . $order->order_number . ' (Disetujui Supervisor: ' . $supervisor->name . ') - ' . ($reason ?: 'Batal transaksi'),
                        'order_id'         => $order->id,
                        'source'           => 'pos',
                        'is_reversed'      => true,
                    ]);
                }
            });

            $this->dispatch('order-voided');
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Transaksi #' . $order->order_number . ' berhasil dibatalkan (Disetujui Supervisor: ' . $supervisor->name . ').'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
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
                    ->whereRaw('(CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) > 0')
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
            $sessionOrders = Order::with(['items', 'voidBy'])
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
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(grand_total) as total_spent'),
                    DB::raw('MAX(created_at) as last_visit')
                )
                ->groupBy('customer_name', 'customer_phone')
                ->orderByDesc('total_spent')
                ->get();

            /* ---------- Rekap Kas ---------- */
            $validOrders  = $sessionOrders->where('status', '!=', 'cancelled');
            $cashSales    = $validOrders->where('payment_method', 'cash')->sum('grand_total');
            $nonCashSales = $validOrders->where('payment_method', '!=', 'cash')->sum('grand_total');

            $sessionPettyCash = \App\Models\Cashflow::where('source', 'pos')
                ->where('category', 'pos_petty_cash')
                ->where('created_at', '>=', $this->activeSession->opened_at)
                ->latest()
                ->get();

            $pettyCashIn  = $sessionPettyCash->where('type', 'in')->sum('amount');
            $pettyCashOut = $sessionPettyCash->where('type', 'out')->sum('amount');

            $sessionStats = [
                'total_trx'      => $validOrders->count(),
                'cash_sales'     => $cashSales,
                'non_cash_sales' => $nonCashSales,
                'total_sales'    => $cashSales + $nonCashSales,
                'opening_cash'   => $this->activeSession->opening_cash,
                'petty_cash_in'  => $pettyCashIn,
                'petty_cash_out' => $pettyCashOut,
                'expected_cash'  => $this->activeSession->opening_cash + $cashSales + $pettyCashIn - $pettyCashOut,
                'opened_at'      => $this->activeSession->opened_at->format('d M Y, H:i'),
            ];
        } else {
            $sessionPettyCash = collect();
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

        $supervisorsList = User::whereNotNull('pos_pin')->get()->filter(function ($u) {
            return $u->hasAnyRole(['super_admin', 'owner', 'manager', 'finance']) || in_array($u->role, ['super_admin', 'owner', 'manager', 'finance']);
        })->map(function ($u) {
            return [
                'id'   => $u->id,
                'name' => $u->name,
                'role' => strtoupper($u->roles->pluck('name')->first() ?? $u->role ?? 'SUPERVISOR'),
            ];
        })->values();

        return view('livewire.pos-manager', [
            'products'         => $products,
            'vouchers'         => $vouchers,
            'paymentMethods'   => $paymentMethods,
            'sessionOrders'    => $sessionOrders,
            'sessionCustomers' => $sessionCustomers,
            'sessionPettyCash' => $sessionPettyCash,
            'sessionStats'     => $sessionStats,
            'supervisorsList'  => $supervisorsList,
        ]);
    }
}
